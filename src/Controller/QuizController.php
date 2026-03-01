<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'quiz')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        
        // Si aucun utilisateur n'est connecté, rediriger vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        $questions = $entityManager->getRepository(Question::class)->findAll();
        
        // Récupérer les réponses de l'utilisateur depuis la session
        $session = $request->getSession();
        $userResponses = $session->get('quiz_responses_' . $user->getId(), []);
        
        // Déterminer si l'utilisateur peut répondre (étudiant) ou seulement voir (admin/teacher)
        $canAnswer = in_array('ROLE_STUDENT', $user->getRoles());
        
        return $this->render('quiz/quiz.html.twig', [
            'questions' => $questions,
            'currentUser' => $user,
            'userResponses' => $userResponses,
            'canAnswer' => $canAnswer,
        ]);
    }
    
    #[Route('/attestation/view', name: 'attestation_view')]
    public function viewAttestation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que c'est un étudiant
        if (!in_array('ROLE_STUDENT', $user->getRoles())) {
            $this->addFlash('error', 'Seuls les étudiants peuvent voir leur attestation');
            return $this->redirectToRoute('quiz');
        }
        
        // Récupérer les questions et réponses
        $questions = $entityManager->getRepository(Question::class)->findAll();
        $session = $request->getSession();
        $userResponses = $session->get('quiz_responses_' . $user->getId(), []);
        
        // Calculer les statistiques
        $totalQuestions = count($questions);
        $answeredQuestions = count($userResponses);
        $totalScore = 0;
        $maxPossibleScore = 0;
        
        foreach ($questions as $question) {
            $maxPossibleScore += $question->getNoteMax();
            $questionKey = 'question_' . $question->getId();
            if (isset($userResponses[$questionKey])) {
                $totalScore += $userResponses[$questionKey]['score'];
            }
        }
        
        $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100) : 0;
        $isCertified = ($answeredQuestions == $totalQuestions) && ($percentage >= 50);
        
        // Déterminer la mention
        $mention = 'Non certifié';
        if ($isCertified) {
            if ($percentage >= 90) $mention = 'Très Bien';
            elseif ($percentage >= 75) $mention = 'Bien';
            elseif ($percentage >= 60) $mention = 'Assez Bien';
            elseif ($percentage >= 50) $mention = 'Passable';
        }
        
        return $this->render('attestation/view.html.twig', [
            'user' => $user,
            'totalQuestions' => $totalQuestions,
            'answeredQuestions' => $answeredQuestions,
            'totalScore' => $totalScore,
            'maxPossibleScore' => $maxPossibleScore,
            'percentage' => $percentage,
            'isCertified' => $isCertified,
            'mention' => $mention,
            'currentUser' => $user
        ]);
    }

    #[Route('/attestation/send-email', name: 'attestation_send_email', methods: ['POST'])]
    public function sendAttestationByEmail(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Utilisateur non connecté'
            ], 401);
        }
        
        // Vérifier que c'est un étudiant
        if (!in_array('ROLE_STUDENT', $user->getRoles())) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Seuls les étudiants peuvent recevoir une attestation'
            ], 403);
        }
        
        // Récupérer les questions et réponses
        $questions = $entityManager->getRepository(Question::class)->findAll();
        $session = $request->getSession();
        $userResponses = $session->get('quiz_responses_' . $user->getId(), []);
        
        // Calculer les statistiques
        $totalQuestions = count($questions);
        $answeredQuestions = count($userResponses);
        $totalScore = 0;
        $maxPossibleScore = 0;
        
        foreach ($questions as $question) {
            $maxPossibleScore += $question->getNoteMax();
            $questionKey = 'question_' . $question->getId();
            if (isset($userResponses[$questionKey])) {
                $totalScore += $userResponses[$questionKey]['score'];
            }
        }
        
        $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100) : 0;
        $isCertified = ($answeredQuestions == $totalQuestions) && ($percentage >= 50);
        
        // Déterminer la mention
        $mention = 'Non certifié';
        if ($isCertified) {
            if ($percentage >= 90) $mention = 'Très Bien';
            elseif ($percentage >= 75) $mention = 'Bien';
            elseif ($percentage >= 60) $mention = 'Assez Bien';
            elseif ($percentage >= 50) $mention = 'Passable';
        }
        
        // Générer le contenu HTML de l'attestation
        $htmlContent = $this->renderView('attestation/email_template.html.twig', [
            'user' => $user,
            'totalQuestions' => $totalQuestions,
            'answeredQuestions' => $answeredQuestions,
            'totalScore' => $totalScore,
            'maxPossibleScore' => $maxPossibleScore,
            'percentage' => $percentage,
            'isCertified' => $isCertified,
            'mention' => $mention,
            'date' => new \DateTime()
        ]);
        
        try {
            // Utiliser le NOUVEAU compte Path2Learn pour les attestations
            $dsn = $_ENV['MAILER_DSN_PATH2LEARN'];
            $transport = Transport::fromDsn($dsn);
            $path2learnMailer = new Mailer($transport);
            
            // Créer l'email avec le nouveau compte
            $email = (new Email())
                ->from('Path2Learn <path2learn.contact@gmail.com>')
                ->to($user->getEmail())
                ->subject('Votre attestation Path2Learn - ' . $user->getPrenom() . ' ' . $user->getNom())
                ->html($htmlContent);
            
            $path2learnMailer->send($email);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Attestation envoyée à ' . $user->getEmail()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Erreur lors de l\'envoi : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/quiz/qr/scanner', name: 'quiz_qr_scanner')]
    public function scanner(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que c'est un étudiant
        if (!in_array('ROLE_STUDENT', $user->getRoles())) {
            $this->addFlash('warning', 'Seuls les étudiants peuvent scanner les QR codes');
            return $this->redirectToRoute('quiz');
        }
        
        return $this->render('quiz/qr_scanner.html.twig', [
            'currentUser' => $user
        ]);
    }

    #[Route('/quiz/qr/batch', name: 'quiz_qr_batch')]
    public function batch(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que c'est un enseignant ou admin
        if (!in_array('ROLE_TEACHER', $user->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('error', 'Accès non autorisé');
            return $this->redirectToRoute('quiz');
        }
        
        $questions = $entityManager->getRepository(Question::class)->findAll();
        
        return $this->render('quiz/qr_batch.html.twig', [
            'questions' => $questions,
            'currentUser' => $user
        ]);
    }

    #[Route('/quiz/play/{id}', name: 'quiz_play')]
    public function play(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Déterminer si l'utilisateur peut répondre (étudiant) ou seulement voir (admin/teacher)
        $canAnswer = in_array('ROLE_STUDENT', $user->getRoles());
        
        $question = $entityManager->getRepository(Question::class)->find($id);
        
        if (!$question) {
            throw $this->createNotFoundException('Question non trouvée.');
        }
        
        // Vérifier si l'utilisateur a déjà répondu via la session
        $session = $request->getSession();
        $userResponses = $session->get('quiz_responses_' . $user->getId(), []);
        $questionKey = 'question_' . $id;
        $existingResponse = $userResponses[$questionKey] ?? null;
        
        // Si l'utilisateur a déjà répondu et que c'est un étudiant, on le met en mode consultation
        if ($existingResponse && $canAnswer) {
            $this->addFlash('info', 'Vous avez déjà répondu à cette question. Mode consultation uniquement.');
        }
        
        // Compter le nombre de réponses correctes
        $correctCount = 0;
        foreach ($question->getChoix() as $choice) {
            if ($choice->isEstCorrect()) {
                $correctCount++;
            }
        }
        
        return $this->render('quiz/play.html.twig', [
            'question' => $question,
            'currentUser' => $user,
            'correctCount' => $correctCount,
            'existingResponse' => $existingResponse,
            'canAnswer' => $canAnswer,
        ]);
    }

   #[Route('/quiz/check-answer', name: 'quiz_check_answer', methods: ['POST'])]
public function checkAnswer(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $user = $this->getUser();

    if (!$user) {
        return new JsonResponse(['success' => false, 'error' => 'Utilisateur non connecté'], 401);
    }

    if (!in_array('ROLE_STUDENT', $user->getRoles())) {
        return new JsonResponse(['success' => false, 'error' => 'Seuls les étudiants peuvent répondre'], 403);
    }

    $data = json_decode($request->getContent(), true);

    $questionId = $data['questionId'] ?? null;
    $selectedChoicesIds = $data['selectedChoicesIds'] ?? [];
    $hintUsed = $data['hintUsed'] ?? false; // ← NOUVEAU

    if (!$questionId) {
        return new JsonResponse(['success' => false, 'error' => 'Données manquantes'], 400);
    }

    $question = $entityManager->getRepository(Question::class)->find($questionId);

    if (!$question) {
        return new JsonResponse(['success' => false, 'error' => 'Question non trouvée'], 404);
    }

    $session = $request->getSession();
    $userResponses = $session->get('quiz_responses_' . $user->getId(), []);
    $questionKey = 'question_' . $questionId;

    if (isset($userResponses[$questionKey])) {
        return new JsonResponse(['success' => false, 'error' => 'Vous avez déjà répondu'], 403);
    }

    $allCorrectChoiceIds = [];
    $correctCount = 0;

    foreach ($question->getChoix() as $choice) {
        if ($choice->isEstCorrect()) {
            $allCorrectChoiceIds[] = $choice->getId();
            $correctCount++;
        }
    }

    $score = $this->calculateScore(
        $selectedChoicesIds,
        $allCorrectChoiceIds,
        $question->getNoteMax(),
        $correctCount
    );

    // ← NOUVEAU : Pénalité -25% si hint utilisé et bonne réponse
    $penalite = 0;
    if ($hintUsed && $score > 0) {
        $penalite = (int) ceil($question->getNoteMax() * 0.25);
        $score = max(0, $score - $penalite);
    }

    $responseData = [
        'selectedChoiceIds' => array_map('intval', $selectedChoicesIds),
        'isCorrect'         => $score > 0,
        'score'             => $score,
        'maxScore'          => $question->getNoteMax(),
        'correctChoiceIds'  => $allCorrectChoiceIds,
        'correctCount'      => $correctCount,
        'hintUsed'          => $hintUsed, // ← NOUVEAU
        'timestamp'         => time()
    ];

    $userResponses[$questionKey] = $responseData;
    $session->set('quiz_responses_' . $user->getId(), $userResponses);

    return new JsonResponse([
        'success'           => true,
        'score'             => $score,
        'maxScore'          => $question->getNoteMax(),
        'correctChoiceIds'  => $allCorrectChoiceIds,
        'selectedChoiceIds' => $selectedChoicesIds,
        'correctCount'      => $correctCount,
        'isCorrect'         => $score > 0,
        'hintUsed'          => $hintUsed,   // ← NOUVEAU
        'penalite'          => $penalite,   // ← NOUVEAU
        'message'           => $score > 0 ? 'Bonne réponse !' : 'Mauvaise réponse.'
    ]);
}
    
    #[Route('/quiz/user-responses', name: 'quiz_user_responses', methods: ['GET'])]
    public function getUserResponses(Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse([], 401);
        }
        
        $session = $request->getSession();
        $userResponses = $session->get('quiz_responses_' . $user->getId(), []);
        
        return new JsonResponse($userResponses);
    }
    
    /**
     * Calcule le score selon votre logique
     */
    private function calculateScore(
        array $selectedChoices, 
        array $correctChoices, 
        int $maxScore, 
        int $correctCount
    ): int {
        // Cas 1: Aucune réponse cochée
        if (empty($selectedChoices)) {
            return 0;
        }
        
        // Convertir en entiers pour la comparaison
        $selectedChoices = array_map('intval', $selectedChoices);
        $correctChoices = array_map('intval', $correctChoices);
        
        // Identifier les bonnes et mauvaises réponses sélectionnées
        $correctSelected = array_intersect($selectedChoices, $correctChoices);
        $incorrectSelected = array_diff($selectedChoices, $correctChoices);
        
        // Cas 2: Une réponse fausse cochée (même si une bonne réponse est cochée)
        if (!empty($incorrectSelected)) {
            return 0;
        }
        
        // Cas 3: Question à choix multiples, utilisateur n'en coche qu'une seule
        if ($correctCount > 1 && count($selectedChoices) < count($correctChoices)) {
            return 0;
        }
        
        // Cas 5: EXACTEMENT toutes les bonnes réponses cochées et aucune mauvaise
        if (count($selectedChoices) === count($correctChoices) && 
            count($correctSelected) === count($correctChoices) && 
            empty($incorrectSelected)) {
            return $maxScore;
        }
        
        // Si c'est une question à choix unique (une seule bonne réponse)
        if ($correctCount === 1 && count($selectedChoices) === 1 && 
            in_array($selectedChoices[0], $correctChoices)) {
            return $maxScore;
        }
        
        // Par défaut, 0
        return 0;
    }
    #[Route('/quiz/ai-hint/{id}', name: 'quiz_ai_hint', methods: ['GET'])]
public function getAIHint(
    int $id,
    EntityManagerInterface $entityManager,
    Request $request,
    \App\Service\AIService $aiService
): JsonResponse {
    $user = $this->getUser();

    if (!$user || !in_array('ROLE_STUDENT', $user->getRoles())) {
        return new JsonResponse(['success' => false, 'error' => 'Accès refusé'], 403);
    }

    $question = $entityManager->getRepository(Question::class)->find($id);

    if (!$question) {
        return new JsonResponse(['success' => false, 'error' => 'Question non trouvée'], 404);
    }
    // Bloquer le hint si déjà répondu
    $session = $request->getSession();
    $userResponses = $session->get('quiz_responses_' . $user->getId(), []);

    if (isset($userResponses['question_' . $id])) {
        return new JsonResponse([
            'success' => false,
            'error' => 'Vous avez déjà répondu à cette question'
        ], 403);
    }

    $hint = $aiService->generateHint(
        $question->getTitre(),
        $question->getDescription(),
        $question->getChoix()->toArray()
    );

    return new JsonResponse([
        'success' => true,
        'hint' => $hint
    ]);
}
}