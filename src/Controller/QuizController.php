<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'quiz')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $questions = $entityManager->getRepository(Question::class)->findAll();
        
        return $this->render('quiz/quiz.html.twig', [
            'questions' => $questions,
            'users' => $users,
        ]);
    }

    #[Route('/quiz/play/{id}', name: 'quiz_play')]
    public function play(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $userId = $request->query->get('user');
        
        if (!$userId) {
            $this->addFlash('warning', 'Veuillez sélectionner un utilisateur pour commencer le quiz.');
            return $this->redirectToRoute('quiz');
        }
        
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('quiz');
        }
        
        $question = $entityManager->getRepository(Question::class)->find($id);
        
        if (!$question) {
            throw $this->createNotFoundException('Question non trouvée.');
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
            'selectedUserId' => $userId,
            'selectedUser' => $user,
            'correctCount' => $correctCount,
        ]);
    }

    #[Route('/quiz/check-answer', name: 'quiz_check_answer', methods: ['POST'])]
    public function checkAnswer(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $questionId = $data['questionId'] ?? null;
        $selectedChoicesIds = $data['selectedChoicesIds'] ?? [];
        $userId = $data['userId'] ?? null;
        
        if (!$questionId || !$userId) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Données manquantes'
            ], 400);
        }
        
        $question = $entityManager->getRepository(Question::class)->find($questionId);
        
        if (!$question) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Question non trouvée'
            ], 404);
        }
        
        // Récupérer toutes les réponses correctes
        $allCorrectChoiceIds = [];
        $correctCount = 0;
        
        foreach ($question->getChoix() as $choice) {
            if ($choice->isEstCorrect()) {
                $allCorrectChoiceIds[] = $choice->getId();
                $correctCount++;
            }
        }
        
        // Appliquer la logique de notation
        $score = $this->calculateScore(
            $selectedChoicesIds,
            $allCorrectChoiceIds,
            $question->getNoteMax(),
            $correctCount
        );
        
        return new JsonResponse([
            'success' => true,
            'score' => $score,
            'maxScore' => $question->getNoteMax(),
            'correctChoiceIds' => $allCorrectChoiceIds,
            'selectedChoiceIds' => $selectedChoicesIds,
            'correctCount' => $correctCount,
            'isCorrect' => $score > 0,
            'message' => $score > 0 ? 'Bonne réponse !' : 'Mauvaise réponse.'
        ]);
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
}