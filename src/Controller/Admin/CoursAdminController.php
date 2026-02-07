<?php

namespace App\Controller\Admin;

use App\Entity\Cours;
use App\Entity\RessourcePedagogique;
use App\Form\CoursType;
use App\Form\RessourcePedagogiqueType;
use App\Repository\CoursRepository;
use App\Repository\RessourcePedagogiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/cours')]
class CoursAdminController extends AbstractController
{
    #[Route('/', name: 'admin_cours_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        // Initialisation des variables
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAll();
        
        // ========== GESTION DU FORMULAIRE COURS ==========
        $cours = new Cours();
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
                    // Définir la date de création si non définie
                    if (!$cours->getDateCreation()) {
                        $cours->setDateCreation(new \DateTime());
                    }
                    
                    $entityManager->persist($cours);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Cours ajouté avec succès !');
                    return $this->redirectToRoute('admin_cours_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout du cours : ' . $e->getMessage());
                }
            } else {
                // Afficher les erreurs de validation
                $errors = $formCours->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        // ========== GESTION DU FORMULAIRE RESSOURCE ==========
        $ressource = new RessourcePedagogique();
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
                    // Définir la date d'ajout si non définie
                    if (!$ressource->getDateAjout()) {
                        $ressource->setDateAjout(new \DateTime());
                    }
                    
                    $entityManager->persist($ressource);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Ressource ajoutée avec succès !');
                    return $this->redirectToRoute('admin_cours_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout de la ressource : ' . $e->getMessage());
                }
            } else {
                // Afficher les erreurs de validation
                $errors = $formRessource->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('cours_admin/index_cours.html.twig', [
            'formCours' => $formCours->createView(),
            'formRessource' => $formRessource->createView(),
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'selectedCours' => null,
            'editForm' => null,
            'selectedRessource' => null,
            'editRessourceForm' => null,
            'active_tab' => 'cours', // AJOUTÉ ICI
        ]);
    }

    #[Route('/view/{id}', name: 'admin_cours_view', methods: ['GET'])]
    public function view(
        Cours $cours,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAll();

        // Créer des formulaires vides
        $formCours = $this->createForm(CoursType::class, new Cours(), [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_cours',
            'csrf_token_id'   => 'cours_item',
        ]);
        
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, new RessourcePedagogique(), [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_ressource',
            'csrf_token_id'   => 'ressource_item',
        ]);

        return $this->render('cours_admin/index_cours.html.twig', [
            'selectedCours' => $cours,
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'formCours' => $formCours->createView(),
            'formRessource' => $formRessource->createView(),
            'editForm' => null,
            'selectedRessource' => null,
            'editRessourceForm' => null,
            'active_tab' => 'cours', // AJOUTÉ ICI
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_cours_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Cours $cours,
        EntityManagerInterface $entityManager,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        $editForm = $this->createForm(CoursType::class, $cours, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_edit_cours',
            'csrf_token_id'   => 'edit_cours_item',
        ]);
        
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le cours a été modifié avec succès !');
                return $this->redirectToRoute('admin_cours_view', ['id' => $cours->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        } elseif ($editForm->isSubmitted() && !$editForm->isValid()) {
            $errors = $editForm->getErrors(true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAll();

        // Créer des formulaires vides
        $formCours = $this->createForm(CoursType::class, new Cours(), [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_cours',
            'csrf_token_id'   => 'cours_item',
        ]);
        
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, new RessourcePedagogique(), [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_ressource',
            'csrf_token_id'   => 'ressource_item',
        ]);

        return $this->render('cours_admin/index_cours.html.twig', [
            'selectedCours' => $cours,
            'editForm' => $editForm->createView(),
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'formCours' => $formCours->createView(),
            'formRessource' => $formRessource->createView(),
            'selectedRessource' => null,
            'editRessourceForm' => null,
            'active_tab' => 'cours', // AJOUTÉ ICI
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_cours_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Cours $cours,
        EntityManagerInterface $entityManager
    ): Response {
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('delete_cours_' . $cours->getId(), $token)) {
            try {
                $entityManager->remove($cours);
                $entityManager->flush();
                $this->addFlash('success', 'Cours supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
        }

        return $this->redirectToRoute('admin_cours_index');
    }

    // ========== RESSOURCES ==========
    // SUPPRIMEZ CES MÉTHODES OU DÉPLACEZ-LES DANS RessourceAdminController
    // Elles sont en conflit avec les routes de RessourceAdminController
}