<?php
// src/Controller/AIQuizController.php

namespace App\Controller;

use App\Entity\Question;
use App\Service\AIService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Changez le préfixe de route pour éviter les conflits
#[Route('/admin/ai-quiz')]  // Changé de '/admin/ai' à '/admin/ai-quiz'
#[IsGranted('ROLE_ADMIN')]
class AIQuizController extends AbstractController
{
    public function __construct(
        private AIService $aiService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    #[Route('/generate-choices/{id}', name: 'ai_quiz_generate_choices', methods: ['POST'])]  // Nom changé
    public function generateChoices(int $id): JsonResponse
    {
        try {
            $question = $this->entityManager->getRepository(Question::class)->find($id);
            
            if (!$question) {
                return $this->json([
                    'success' => false,
                    'message' => 'Question non trouvée'
                ], Response::HTTP_NOT_FOUND);
            }

            $result = $this->aiService->generateChoices(
                $question->getTitre() . ' - ' . $question->getDescription(),
                max(4, $question->getChoix()->count() + 2)
            );

            // Sauvegarder les nouveaux choix
            $nouveauxChoix = 0;
            foreach ($result['choices'] as $choiceData) {
                $choix = new \App\Entity\Choix();
                $choix->setContenu($choiceData['text']);
                $choix->setEstCorrect($choiceData['isCorrect']);
                $choix->setQuestion($question);
                $this->entityManager->persist($choix);
                $nouveauxChoix++;
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $nouveauxChoix . ' choix générés avec Groq AI',
                'explanation' => $result['explanation'] ?? ''
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur AIQuizController: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/explain-answer/{id}', name: 'ai_quiz_explain_answer', methods: ['GET'])]  // Nom changé
    public function explainAnswer(int $id): JsonResponse
    {
        try {
            $question = $this->entityManager->getRepository(Question::class)->find($id);
            
            if (!$question) {
                return $this->json([
                    'success' => false,
                    'message' => 'Question non trouvée'
                ], Response::HTTP_NOT_FOUND);
            }

            $correctChoice = null;
            $allChoices = [];
            
            foreach ($question->getChoix() as $choix) {
                $allChoices[] = [
                    'contenu' => $choix->getContenu(),
                    'estCorrect' => $choix->isEstCorrect()
                ];
                if ($choix->isEstCorrect()) {
                    $correctChoice = $choix;
                }
            }

            if (!$correctChoice) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucune bonne réponse trouvée'
                ], Response::HTTP_BAD_REQUEST);
            }

            $explanation = $this->aiService->generateExplanation(
                $question->getTitre(),
                $correctChoice->getContenu(),
                $allChoices
            );

            return $this->json([
                'success' => true,
                'explanation' => $explanation
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/test', name: 'ai_quiz_test', methods: ['GET'])]  // Nom changé
    public function test(): JsonResponse
    {
        return $this->json([
            'success' => true,
            'message' => 'AIQuizController fonctionne',
            'time' => date('Y-m-d H:i:s')
        ]);
    }
    // Dans AIQuizController.php
#[Route('/test-groq-simple', name: 'ai_test_groq_simple', methods: ['GET'])]
public function testGroqSimple(AIService $aiService): JsonResponse
{
    try {
        $result = $aiService->generateChoices("Quelle est la capitale de la France?", 4);
        return $this->json([
            'success' => true,
            'result' => $result
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
}
#[Route('/debug-service', name: 'ai_quiz_debug_service', methods: ['GET'])]
public function debugService(AIService $aiService): JsonResponse
{
    // Test direct du service
    $result = $aiService->generateChoices("Quelle est la capitale de la France?", 4);
    
    return $this->json([
        'service_result' => $result,
        'using_fallback' => ($result['choices'][0]['text'] ?? '') === 'Option A',
        'api_key_loaded' => !empty($aiService->getApiKey()), // Vous devrez ajouter cette méthode
        'api_url' => $aiService->getApiUrl() // Vous devrez ajouter cette méthode
    ]);
}
#[Route('/analyze-difficulty/{id}', name: 'ai_quiz_analyze_difficulty', methods: ['GET'])]
public function analyzeDifficulty(int $id): JsonResponse
{
    try {
        $question = $this->entityManager->getRepository(Question::class)->find($id);

        if (!$question) {
            return $this->json([
                'success' => false,
                'message' => 'Question non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($question->getChoix()->count() === 0) {
            return $this->json([
                'success' => false,
                'message' => 'Ajoutez des choix avant d\'analyser la difficulté'
            ], Response::HTTP_BAD_REQUEST);
        }

        $allChoices = [];
        foreach ($question->getChoix() as $choix) {
            $allChoices[] = [
                'contenu' => $choix->getContenu(),
                'estCorrect' => $choix->isEstCorrect()
            ];
        }

        $result = $this->aiService->analyzeDifficulty(
            $question->getTitre() . ' - ' . $question->getDescription(),
            $allChoices
        );

        return $this->json([
            'success' => true,
            'data' => $result
        ]);

    } catch (\Exception $e) {
        $this->logger->error('Erreur analyze difficulty: ' . $e->getMessage());
        return $this->json([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
}