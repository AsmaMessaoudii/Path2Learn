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
    #[Route('/admin/quiz', name: 'quiz_admin')]
    public function quizAdmin(Request $request, EntityManagerInterface $em): Response
    {
        // Ajouter une question
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Lier l'utilisateur connecté
            $user = $this->getUser();
            if ($user) {
                $question->setUser($user);
            }
            $question->setDateCreation(new \DateTime());
            $em->persist($question);
            $em->flush();

            $this->addFlash('success', 'Question ajoutée avec succès !');
            return $this->redirectToRoute('quiz_admin');
        }

        $questions = $em->getRepository(Question::class)->findAll();

        return $this->render('admin/quiz_admin.html.twig', [
            'questions' => $questions,
            'form' => $form->createView(),
        ]);
    }
}
