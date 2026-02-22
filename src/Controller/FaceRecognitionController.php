<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class FaceRecognitionController extends AbstractController
{
    private const FACE_API_URL = 'http://127.0.0.1:5000';

    public function __construct(
        private HttpClientInterface $httpClient,
        private UserRepository $userRepository,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
        private MailerInterface $mailer
    ) {}

    #[Route('/face-login', name: 'app_face_login')]
    public function faceLogin(): Response
    {
        return $this->render('face_recognition/login.html.twig');
    }

    #[Route('/face-register', name: 'app_face_register')]
    public function faceRegister(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('face_recognition/register.html.twig');
    }

    #[Route('/face-verify-2fa', name: 'app_face_verify_2fa')]
    public function verify2FA(): Response
    {
        // Vérifier qu'il y a une tentative de connexion en cours
        $session = $this->requestStack->getSession();
        if (!$session->has('face_2fa_user_id')) {
            return $this->redirectToRoute('app_face_login');
        }

        return $this->render('face_recognition/verify_2fa.html.twig');
    }

    #[Route('/api/face/register', name: 'api_face_register', methods: ['POST'])]
    public function registerFace(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $data = json_decode($request->getContent(), true);
        $imageData = $data['image'] ?? null;

        if (!$imageData) {
            return new JsonResponse(['error' => 'No image provided'], 400);
        }

        try {
            $user = $this->getUser();
            
            $response = $this->httpClient->request('POST', self::FACE_API_URL . '/register', [
                'json' => [
                    'user_id' => $user->getId(),
                    'image' => $imageData
                ],
                'timeout' => 30
            ]);

            $result = $response->toArray();

            if (isset($result['success']) && $result['success']) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Visage enregistré avec succès!'
                ]);
            }

            return new JsonResponse([
                'success' => false,
                'error' => $result['error'] ?? 'Erreur inconnue'
            ], 400);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/face/recognize', name: 'api_face_recognize', methods: ['POST'])]
    public function recognizeFace(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $imageData = $data['image'] ?? null;

        if (!$imageData) {
            return new JsonResponse(['error' => 'No image provided'], 400);
        }

        try {
            $response = $this->httpClient->request('POST', self::FACE_API_URL . '/recognize', [
                'json' => [
                    'image' => $imageData
                ],
                'timeout' => 30
            ]);

            $result = $response->toArray();

            if ($result['success'] ?? false) {
                $userId = $result['user_id'];
                $user = $this->userRepository->find($userId);

                if ($user) {
                    // Générer code 2FA
                    $code2FA = sprintf('%06d', random_int(0, 999999));
                    
                    // Stocker en session
                    $session = $this->requestStack->getSession();
                    $session->set('face_2fa_code', $code2FA);
                    $session->set('face_2fa_user_id', $user->getId());
                    $session->set('face_2fa_expires', time() + 300); // 5 minutes
                    
                    // Envoyer l'email
                    $this->send2FAEmail($user, $code2FA);
                    
                    return new JsonResponse([
                        'success' => true,
                        'require_2fa' => true,
                        'user' => [
                            'prenom' => $user->getPrenom(),
                            'nom' => $user->getNom(),
                            'email' => $user->getEmail()
                        ],
                        'confidence' => $result['confidence'],
                        'redirect' => $this->generateUrl('app_face_verify_2fa')
                    ]);
                }
            }

            return new JsonResponse([
                'success' => false,
                'message' => $result['message'] ?? 'Visage non reconnu'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/face/verify-2fa', name: 'api_face_verify_2fa', methods: ['POST'])]
    public function verify2FACode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? null;

        if (!$code) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Code manquant'
            ], 400);
        }

        $session = $this->requestStack->getSession();
        
        // Vérifier l'expiration
        if (!$session->has('face_2fa_expires') || time() > $session->get('face_2fa_expires')) {
            $session->remove('face_2fa_code');
            $session->remove('face_2fa_user_id');
            $session->remove('face_2fa_expires');
            
            return new JsonResponse([
                'success' => false,
                'error' => 'Code expiré. Veuillez recommencer.'
            ], 400);
        }

        // Vérifier le code
        $validCode = $session->get('face_2fa_code');
        $userId = $session->get('face_2fa_user_id');

        if ($code !== $validCode) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Code incorrect'
            ], 400);
        }

        // Code valide - connecter l'utilisateur
        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Utilisateur introuvable'
            ], 404);
        }

        // Connexion
        $token = new UsernamePasswordToken(
            $user,
            'main',
            $user->getRoles()
        );
        
        $this->tokenStorage->setToken($token);
        $session->set('_security_main', serialize($token));
        
        // Nettoyer la session
        $session->remove('face_2fa_code');
        $session->remove('face_2fa_user_id');
        $session->remove('face_2fa_expires');

        return new JsonResponse([
            'success' => true,
            'message' => 'Connexion réussie!',
            'redirect' => $this->generateUrl('home')
        ]);
    }

    #[Route('/api/face/resend-2fa', name: 'api_face_resend_2fa', methods: ['POST'])]
    public function resend2FA(): JsonResponse
    {
        $session = $this->requestStack->getSession();
        
        if (!$session->has('face_2fa_user_id')) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Session expirée'
            ], 400);
        }

        $userId = $session->get('face_2fa_user_id');
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Utilisateur introuvable'
            ], 404);
        }

        // Générer nouveau code
        $code2FA = sprintf('%06d', random_int(0, 999999));
        
        $session->set('face_2fa_code', $code2FA);
        $session->set('face_2fa_expires', time() + 300);
        
        $this->send2FAEmail($user, $code2FA);

        return new JsonResponse([
            'success' => true,
            'message' => 'Nouveau code envoyé!'
        ]);
    }

    #[Route('/api/face/delete', name: 'api_face_delete', methods: ['POST'])]
    public function deleteFace(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        try {
            $user = $this->getUser();
            $userId = $user->getId();
            
            $response = $this->httpClient->request('POST', self::FACE_API_URL . '/delete', [
                'json' => [
                    'user_id' => $userId
                ],
                'timeout' => 30
            ]);

            $result = $response->toArray();

            if (isset($result['success']) && $result['success']) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Face ID supprimé avec succès!'
                ]);
            }

            return new JsonResponse([
                'success' => false,
                'error' => $result['error'] ?? 'Erreur lors de la suppression'
            ], 400);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/face/check', name: 'api_face_check', methods: ['GET'])]
    public function checkFaceRegistered(): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        try {
            $user = $this->getUser();
            $userId = $user->getId();
            
            $response = $this->httpClient->request('GET', self::FACE_API_URL . '/check/' . $userId, [
                'timeout' => 30
            ]);

            $result = $response->toArray();

            return new JsonResponse([
                'registered' => $result['exists'] ?? false
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'registered' => false
            ]);
        }
    }

    private function send2FAEmail($user, string $code): void
    {
        try {
            $email = (new Email())
                ->from('nawresjouini38@gmail.com')
                ->to($user->getEmail())
                ->subject('🔐 Code de vérification Face ID - Path2Learn')
                ->html("
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #667eea;'>🔐 Vérification en deux étapes</h2>
                        <p>Bonjour <strong>{$user->getPrenom()} {$user->getNom()}</strong>,</p>
                        <p>Vous tentez de vous connecter avec Face ID. Pour finaliser votre connexion, veuillez entrer ce code :</p>
                        <div style='background: #f8f9fa; padding: 20px; text-align: center; border-radius: 10px; margin: 20px 0;'>
                            <h1 style='color: #667eea; font-size: 48px; letter-spacing: 10px; margin: 0;'>{$code}</h1>
                        </div>
                        <p><strong>⏰ Ce code expire dans 5 minutes.</strong></p>
                        <p style='color: #6c757d; font-size: 12px;'>
                            Si vous n'avez pas demandé ce code, ignorez cet email et changez votre mot de passe immédiatement.
                        </p>
                        <hr style='border: 1px solid #e9ecef; margin: 30px 0;'>
                        <p style='color: #6c757d; font-size: 12px; text-align: center;'>
                            Path2Learn - Plateforme d'apprentissage<br>
                            © 2026 Tous droits réservés
                        </p>
                    </div>
                ");

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer
            error_log('Erreur envoi email 2FA: ' . $e->getMessage());
        }
    }
}