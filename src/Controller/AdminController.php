<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

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

        $questions = $entityManager->getRepository(Question::class)->findAll();

        return $this->render('admin/quiz_admin.html.twig', [
            'questions' => $questions,
            'form' => $form->createView(),
            'editMode' => false,
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

        $questions = $entityManager->getRepository(Question::class)->findAll();

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
}