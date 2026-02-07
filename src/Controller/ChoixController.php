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
    #[Route('/admin/quiz/choix/{questionId}', name: 'add_choix', methods: ['GET', 'POST'])]
    public function addChoix(
        int $questionId, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response
    {
        $question = $entityManager->getRepository(Question::class)->find($questionId);

        if (!$question) {
            $this->addFlash('error', 'Question introuvable.');
            return $this->redirectToRoute('quiz_admin');
        }

        $choix = new Choix();
        $choix->setQuestion($question);

        $form = $this->createForm(ChoixType::class, $choix, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_choix',
            'csrf_token_id'   => 'choix_item',
        ]);
        
        $form->handleRequest($request);
        
        // Vérifier si c'est le formulaire choix qui est soumis
        $isChoixSubmitted = $form->isSubmitted() && $form->getName() === $request->request->get('form_name');
        
        if ($isChoixSubmitted) {
            if ($form->isValid()) {
                try {
                    $entityManager->persist($choix);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Choix ajouté avec succès !');
                    return $this->redirectToRoute('quiz_admin');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout du choix : ' . $e->getMessage());
                }
            } else {
                // Afficher les erreurs de validation
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/choix_form.html.twig', [
            'form' => $form->createView(),
            'question' => $question,
            'editMode' => false,
        ]);
    }

    #[Route('/admin/choix/{id}/edit', name: 'edit_choix', methods: ['GET', 'POST'])]
    public function editChoix(
        Choix $choix, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(ChoixType::class, $choix, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_edit_choix',
            'csrf_token_id'   => 'edit_choix_item',
        ]);
        
        $form->handleRequest($request);
        
        // Vérifier si c'est le formulaire choix qui est soumis
        $isChoixSubmitted = $form->isSubmitted() && $form->getName() === $request->request->get('form_name');
        
        if ($isChoixSubmitted) {
            if ($form->isValid()) {
                try {
                    $entityManager->flush();
                    $this->addFlash('success', 'Choix modifié avec succès !');
                    return $this->redirectToRoute('quiz_admin');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
                }
            } else {
                // Afficher les erreurs de validation
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/choix_form.html.twig', [
            'form' => $form->createView(),
            'question' => $choix->getQuestion(),
            'editMode' => true,
        ]);
    }

    #[Route('/admin/choix/{id}/delete', name: 'delete_choix', methods: ['POST'])]
    public function deleteChoix(
        Choix $choix, 
        EntityManagerInterface $entityManager, 
        Request $request
    ): Response
    {
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('delete_choix_' . $choix->getId(), $token)) {
            try {
                $entityManager->remove($choix);
                $entityManager->flush();
                $this->addFlash('success', 'Choix supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
        }

        return $this->redirectToRoute('quiz_admin');
    }
}