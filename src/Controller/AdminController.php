<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;


class AdminController extends AbstractController
{
    #[Route('/admin/quiz', name: 'quiz_admin', methods: ['GET', 'POST'])]
    public function quizAdmin(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Initialisation
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_question',
            'csrf_token_id'   => 'question_item',
        ]);
        
        $form->handleRequest($request);
        
        // Vérifier si c'est le formulaire question qui est soumis
        $isQuestionSubmitted = $form->isSubmitted() && $form->getName() === $request->request->get('form_name');
        
        if ($isQuestionSubmitted) {
            if ($form->isValid()) {
                try {
                    // Lier l'utilisateur connecté
                    $user = $this->getUser();
                    if ($user) {
                        $question->setUser($user);
                    }
                    $question->setDateCreation(new \DateTime());
                    $entityManager->persist($question);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Question ajoutée avec succès !');
                    return $this->redirectToRoute('quiz_admin');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout de la question : ' . $e->getMessage());
                }
            } else {
                // Afficher les erreurs de validation
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        

        // Tri par défaut : alphabétique croissant
        $questions = $entityManager->getRepository(Question::class)->findBy(
            [],
            ['titre' => 'ASC']
        );

        return $this->render('admin/quiz_admin.html.twig', [
            'questions' => $questions,
            'form' => $form->createView(),
            'editMode' => false,
        ]);
    }
    

    #[Route('/admin/quiz/search', name: 'quiz_search_ajax', methods: ['GET'])]
    public function searchQuestionsAjax(
        Request $request,
        QuestionRepository $questionRepository
    ): JsonResponse
    {
        $searchTerm = $request->query->get('search', '');
        $searchType = $request->query->get('type', 'titre');
        $sortOrder = $request->query->get('sort', 'asc');
        $sortBy = $request->query->get('sortBy', 'titre');
        
        $questions = $questionRepository->searchByCriteria($searchTerm, $searchType, $sortBy, $sortOrder);
        
        $html = $this->renderView('admin/_question_list.html.twig', [
            'questions' => $questions
        ]);
        
        return new JsonResponse([
            'html' => $html,
            'count' => count($questions)
        ]);
    }
    #[Route('/admin/question/{id}/edit', name: 'edit_question', methods: ['GET', 'POST'])]
    public function editQuestion(
        Question $question,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $editForm = $this->createForm(QuestionType::class, $question, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_edit_question',
            'csrf_token_id'   => 'edit_question_item',
        ]);
        
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Question modifiée avec succès !');
                return $this->redirectToRoute('quiz_admin');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        } elseif ($editForm->isSubmitted() && !$editForm->isValid()) {
            $errors = $editForm->getErrors(true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        $questions = $entityManager->getRepository(Question::class)->findBy(
            [],
            ['titre' => 'ASC']
        );

        return $this->render('admin/quiz_admin.html.twig', [
            'questions' => $questions,
            'form' => $editForm->createView(),
            'editMode' => true,
            'selectedQuestion' => $question,
        ]);
    }

    #[Route('/admin/question/{id}/delete', name: 'delete_question', methods: ['POST'])]
    public function deleteQuestion(
        Question $question,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('delete_question_' . $question->getId(), $token)) {
            try {
                $entityManager->remove($question);
                $entityManager->flush();
                $this->addFlash('success', 'Question supprimée avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
        }

        return $this->redirectToRoute('quiz_admin');
    }
    #[Route('/admin/quiz/pdf', name: 'quiz_pdf')]
public function exportQuizPdf(EntityManagerInterface $entityManager): Response
{
    $questions = $entityManager->getRepository(Question::class)->findAll();

    // Configuration Dompdf
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->setIsRemoteEnabled(true);

    $dompdf = new Dompdf($options);

    // HTML depuis Twig
    $html = $this->renderView('admin/quiz_pdf.html.twig', [
        'questions' => $questions
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Téléchargement
    return new Response(
        $dompdf->output(),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="questions_quiz.pdf"',
        ]
    );
}
#[Route('/admin/stats/questions', name: 'questions_stats', methods: ['GET'])]
public function questionsStats(
    QuestionRepository $questionRepository
): Response
{
    $stats = $questionRepository->getCorrectAnswersStats();
    
    // Calculer les pourcentages et les totaux globaux
    $totalQuestions = count($stats);
    $totalChoix = 0;
    $totalBonnesReponses = 0;
    
    foreach ($stats as &$stat) {
        $stat['pourcentage'] = $stat['totalChoix'] > 0 
            ? round(($stat['bonnesReponses'] / $stat['totalChoix']) * 100, 1)
            : 0;
        
        $totalChoix += $stat['totalChoix'];
        $totalBonnesReponses += $stat['bonnesReponses'];
    }
    
    $pourcentageGlobal = $totalChoix > 0 
        ? round(($totalBonnesReponses / $totalChoix) * 100, 1)
        : 0;
    
    return $this->render('admin/stats_questions.html.twig', [
        'stats' => $stats,
        'totalQuestions' => $totalQuestions,
        'totalChoix' => $totalChoix,
        'totalBonnesReponses' => $totalBonnesReponses,
        'pourcentageGlobal' => $pourcentageGlobal
    ]);
}

#[Route('/admin/stats/question/{id}', name: 'question_detail_stats', methods: ['GET'])]
public function questionDetailStats(
    int $id,
    QuestionRepository $questionRepository
): Response
{
    $stat = $questionRepository->getQuestionStats($id);
    
    if (!$stat) {
        throw $this->createNotFoundException('Question non trouvée');
    }
    
    // Calculer les pourcentages
    $stat['pourcentageBonnes'] = $stat['totalChoix'] > 0 
        ? round(($stat['bonnesReponses'] / $stat['totalChoix']) * 100, 1)
        : 0;
    
    $stat['pourcentageMauvaises'] = $stat['totalChoix'] > 0 
        ? round(($stat['mauvaisesReponses'] / $stat['totalChoix']) * 100, 1)
        : 0;
    
    return $this->render('admin/stats_question_detail.html.twig', [
        'stat' => $stat
    ]);
}



    
    
}