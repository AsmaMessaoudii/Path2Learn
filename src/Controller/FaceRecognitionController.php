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

class FaceRecognitionController extends AbstractController
{
    private const FACE_API_URL = 'http://127.0.0.1:5000';

    public function __construct(
        private HttpClientInterface $httpClient,
        private UserRepository $userRepository,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack
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
            
            // Appeler l'API Python
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
            // Appeler l'API Python
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
                    // ✅ CONNEXION AUTOMATIQUE - C'EST ICI LE FIX!
                    $token = new UsernamePasswordToken(
                        $user,
                        'main', // Le nom de votre firewall (généralement 'main')
                        $user->getRoles()
                    );
                    
                    $this->tokenStorage->setToken($token);
                    
                    // Sauvegarder le token dans la session
                    $session = $this->requestStack->getSession();
                    $session->set('_security_main', serialize($token));
                    
                    return new JsonResponse([
                        'success' => true,
                        'authenticated' => true,
                        'user' => [
                            'id' => $user->getId(),
                            'nom' => $user->getNom(),
                            'prenom' => $user->getPrenom(),
                            'email' => $user->getEmail()
                        ],
                        'confidence' => $result['confidence'],
                        'redirect' => $this->generateUrl('home')
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
}