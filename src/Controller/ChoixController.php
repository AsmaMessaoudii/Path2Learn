<?php

namespace App\Controller;

use App\Entity\Choix;
use App\Entity\Question;
use App\Form\ChoixType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ChoixController extends AbstractController
{
    #[Route('/admin/quiz/choix/{questionId}', name: 'add_choix')]
    public function addChoix(int $questionId, Request $request, EntityManagerInterface $em): Response
    {
        $question = $em->getRepository(Question::class)->find($questionId);

        if (!$question) {
            $this->addFlash('error', 'Question introuvable.');
            return $this->redirectToRoute('quiz_admin');
        }

        $choix = new Choix();
        $form = $this->createForm(ChoixType::class, $choix);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $choix->setQuestion($question);
            $em->persist($choix);
            $em->flush();

            $this->addFlash('success', 'Choix ajouté avec succès !');
            return $this->redirectToRoute('quiz_admin');
        }

        return $this->render('admin/choix_form.html.twig', [
            'form' => $form->createView(),
            'question' => $question,
        ]);
    }
}
