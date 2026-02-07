<?php

namespace App\Controller\Admin;

use App\Entity\RessourcePedagogique;
use App\Entity\Cours;
use App\Form\RessourcePedagogiqueType;
use App\Form\CoursType;
use App\Repository\RessourcePedagogiqueRepository;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/ressources')]
class RessourceAdminController extends AbstractController
{
    #[Route('/', name: 'admin_ressources_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response
    {
        // Initialisation des variables
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAll();
        
        // ========== GESTION DU FORMULAIRE RESSOURCE ==========
        $ressource = new RessourcePedagogique();
        $ressource->setDateAjout(new \DateTime());
        
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, $ressource, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_ressource',
            'csrf_token_id'   => 'ressource_item',
        ]);
        $formRessource->handleRequest($request);
        
        // Vérifier si c'est le formulaire ressource qui est soumis
        $isRessourceSubmitted = $formRessource->isSubmitted() && $formRessource->getName() === $request->request->get('form_name');
        
        if ($isRessourceSubmitted) {
            if ($formRessource->isValid()) {
                try {
                    $em->persist($ressource);
                    $em->flush();
                    
                    $this->addFlash('success', 'La ressource pédagogique a été ajoutée avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout de la ressource : ' . $e->getMessage());
                }
            } else {
                $errors = $formRessource->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        // ========== GESTION DU FORMULAIRE COURS ==========
        $cours = new Cours();
        $cours->setDateCreation(new \DateTime());
        
        $formCours = $this->createForm(CoursType::class, $cours, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_cours',
            'csrf_token_id'   => 'cours_item',
        ]);
        $formCours->handleRequest($request);
        
        // Vérifier si c'est le formulaire cours qui est soumis
        $isCoursSubmitted = $formCours->isSubmitted() && $formCours->getName() === $request->request->get('form_name');
        
        if ($isCoursSubmitted) {
            if ($formCours->isValid()) {
                try {
                    $em->persist($cours);
                    $em->flush();
                    
                    $this->addFlash('success', 'Le cours a été ajouté avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout du cours : ' . $e->getMessage());
                }
            } else {
                $errors = $formCours->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        return $this->render('cours_admin/index_cours.html.twig', [
            'formRessource' => $formRessource->createView(),
            'formCours' => $formCours->createView(),
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'selectedCours' => null,
            'editForm' => null,
            'selectedRessource' => null,
            'editRessourceForm' => null,
            'active_tab' => 'ressources',
        ]);
    }

    #[Route('/view/{id}', name: 'admin_ressources_view', methods: ['GET', 'POST'])]
    public function view(
        RessourcePedagogique $ressource,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository,
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        // Récupérer toutes les données
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAll();
        
        // ========== GESTION DU FORMULAIRE RESSOURCE ==========
        $newRessource = new RessourcePedagogique();
        $newRessource->setDateAjout(new \DateTime());
        
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, $newRessource, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_ressource',
            'csrf_token_id'   => 'ressource_item',
        ]);
        $formRessource->handleRequest($request);
        
        $isRessourceSubmitted = $formRessource->isSubmitted() && $formRessource->getName() === $request->request->get('form_name');
        
        if ($isRessourceSubmitted) {
            if ($formRessource->isValid()) {
                try {
                    $em->persist($newRessource);
                    $em->flush();
                    
                    $this->addFlash('success', 'La ressource pédagogique a été ajoutée avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout de la ressource : ' . $e->getMessage());
                }
            } else {
                $errors = $formRessource->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        // ========== GESTION DU FORMULAIRE COURS ==========
        $cours = new Cours();
        $cours->setDateCreation(new \DateTime());
        
        $formCours = $this->createForm(CoursType::class, $cours, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_cours',
            'csrf_token_id'   => 'cours_item',
        ]);
        $formCours->handleRequest($request);
        
        $isCoursSubmitted = $formCours->isSubmitted() && $formCours->getName() === $request->request->get('form_name');
        
        if ($isCoursSubmitted) {
            if ($formCours->isValid()) {
                try {
                    $em->persist($cours);
                    $em->flush();
                    
                    $this->addFlash('success', 'Le cours a été ajouté avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout du cours : ' . $e->getMessage());
                }
            } else {
                $errors = $formCours->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        return $this->render('cours_admin/index_cours.html.twig', [
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'formRessource' => $formRessource->createView(),
            'formCours' => $formCours->createView(),
            'selectedCours' => null,
            'editForm' => null,
            'selectedRessource' => $ressource,
            'editRessourceForm' => null,
            'active_tab' => 'ressources',
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_ressources_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        RessourcePedagogique $ressource,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository,
        EntityManagerInterface $em
    ): Response
    {
        // Créer le formulaire d'édition
        $editRessourceForm = $this->createForm(RessourcePedagogiqueType::class, $ressource, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_edit_ressource',
            'csrf_token_id'   => 'edit_ressource_item',
        ]);
        $editRessourceForm->handleRequest($request);
        
        // Si le formulaire d'édition est soumis
        if ($editRessourceForm->isSubmitted() && $editRessourceForm->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'La ressource pédagogique a été modifiée avec succès !');
                return $this->redirectToRoute('admin_ressources_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        } elseif ($editRessourceForm->isSubmitted() && !$editRessourceForm->isValid()) {
            $errors = $editRessourceForm->getErrors(true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        
        // Récupérer toutes les données
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAll();
        
        // ========== GESTION DU FORMULAIRE RESSOURCE ==========
        $newRessource = new RessourcePedagogique();
        $newRessource->setDateAjout(new \DateTime());
        
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, $newRessource, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_ressource',
            'csrf_token_id'   => 'ressource_item',
        ]);
        $formRessource->handleRequest($request);
        
        $isRessourceSubmitted = $formRessource->isSubmitted() && $formRessource->getName() === $request->request->get('form_name');
        
        if ($isRessourceSubmitted) {
            if ($formRessource->isValid()) {
                try {
                    $em->persist($newRessource);
                    $em->flush();
                    
                    $this->addFlash('success', 'La ressource pédagogique a été ajoutée avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout de la ressource : ' . $e->getMessage());
                }
            } else {
                $errors = $formRessource->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        // ========== GESTION DU FORMULAIRE COURS ==========
        $cours = new Cours();
        $cours->setDateCreation(new \DateTime());
        
        $formCours = $this->createForm(CoursType::class, $cours, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_cours',
            'csrf_token_id'   => 'cours_item',
        ]);
        $formCours->handleRequest($request);
        
        $isCoursSubmitted = $formCours->isSubmitted() && $formCours->getName() === $request->request->get('form_name');
        
        if ($isCoursSubmitted) {
            if ($formCours->isValid()) {
                try {
                    $em->persist($cours);
                    $em->flush();
                    
                    $this->addFlash('success', 'Le cours a été ajouté avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout du cours : ' . $e->getMessage());
                }
            } else {
                $errors = $formCours->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        return $this->render('cours_admin/index_cours.html.twig', [
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'formRessource' => $formRessource->createView(),
            'formCours' => $formCours->createView(),
            'selectedCours' => null,
            'editForm' => null,
            'selectedRessource' => $ressource,
            'editRessourceForm' => $editRessourceForm->createView(),
            'active_tab' => 'ressources',
        ]);
    }

    #[Route('/{id}', name: 'admin_ressources_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        RessourcePedagogique $ressource, 
        EntityManagerInterface $em
    ): Response
    {
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('delete_ressource_' . $ressource->getId(), $token)) {
            try {
                $em->remove($ressource);
                $em->flush();
                $this->addFlash('success', 'La ressource pédagogique a été supprimée avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
        }
        
        return $this->redirectToRoute('admin_ressources_index');
    }
}