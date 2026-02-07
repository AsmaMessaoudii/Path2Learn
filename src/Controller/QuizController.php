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
        // Récupérer tous les utilisateurs pour la checkbox
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
        // Récupérer le paramètre d'utilisateur depuis l'URL
        $userId = $request->query->get('user');
        
        if (!$userId) {
            $this->addFlash('warning', 'Veuillez sélectionner un utilisateur pour commencer le quiz.');
            return $this->redirectToRoute('quiz');
        }
        
        // Vérifier que l'utilisateur existe
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('quiz');
        }
        
        // Récupérer la question
        $question = $entityManager->getRepository(Question::class)->find($id);
        
        if (!$question) {
            throw $this->createNotFoundException('Question non trouvée.');
        }
        
        return $this->render('quiz/play.html.twig', [
            'question' => $question,
            'selectedUserId' => $userId,
            'selectedUser' => $user,
        ]);
    }

    #[Route('/quiz/check-answer', name: 'quiz_check_answer', methods: ['POST'])]
    public function checkAnswer(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $questionId = $data['questionId'] ?? null;
        $selectedChoiceId = $data['selectedChoiceId'] ?? null;
        $userId = $data['userId'] ?? null;
        
        if (!$questionId || !$selectedChoiceId || !$userId) {
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
        
        $isCorrect = false;
        $correctChoiceId = null;
        
        foreach ($question->getChoix() as $choice) {
            if ($choice->isEstCorrect()) {
                $correctChoiceId = $choice->getId();
            }
            
            if ($choice->getId() == $selectedChoiceId && $choice->isEstCorrect()) {
                $isCorrect = true;
            }
        }
        
        $score = $isCorrect ? $question->getNoteMax() : 0;
        
        return new JsonResponse([
            'success' => true,
            'isCorrect' => $isCorrect,
            'score' => $score,
            'maxScore' => $question->getNoteMax(),
            'correctChoiceId' => $correctChoiceId,
            'userId' => $userId,
            'questionId' => $questionId,
            'message' => $isCorrect ? 'Bonne réponse !' : 'Mauvaise réponse.'
        ]);
    }
}