<?php

namespace App\Controller\Admin;

use App\Entity\RessourcePedagogique;
use App\Form\RessourcePedagogiqueType;
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
    // Supprimez cette méthode ou redirigez vers la page principale
    /*
    #[Route('/', name: 'admin_ressources_index')]
    public function index(RessourcePedagogiqueRepository $ressourceRepository): Response
    {
        $ressourcesList = $ressourceRepository->findAll();

        return $this->render('admin/ressources/index.html.twig', [
            'ressourcesList' => $ressourcesList,
        ]);
    }
    */
    
    // OU redirigez vers la page principale des cours
    #[Route('/', name: 'admin_ressources_index')]
    public function index(): Response
    {
        // Redirige vers la page principale avec l'onglet ressources activé
        return $this->redirectToRoute('admin_cours_index');
    }

    #[Route('/new', name: 'admin_ressources_new')]
    public function new(
        Request $request, 
        EntityManagerInterface $em,
        CoursRepository $coursRepository
    ): Response
    {
        $ressource = new RessourcePedagogique();
        $ressource->setDateAjout(new \DateTime());
        
        $form = $this->createForm(RessourcePedagogiqueType::class, $ressource);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ressource);
            $em->flush();
            
            $this->addFlash('success', 'La ressource pédagogique a été ajoutée avec succès !');
            
            // Redirige vers la page principale avec l'onglet ressources
            return $this->redirectToRoute('admin_cours_index');
        }
        
        // Créez ce template si vous voulez une page séparée
        return $this->render('cours_admin/index_cours.html.twig', [
            'formRessource' => $form->createView(),
            'coursList' => $coursRepository->findAll(),
            'ressourcesList' => $em->getRepository(RessourcePedagogique::class)->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'admin_ressources_delete', methods: ['POST', 'DELETE'])]
    public function delete(
        Request $request, 
        RessourcePedagogique $ressource, 
        EntityManagerInterface $em
    ): Response
    {
        // Vérification CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete'.$ressource->getId(), $request->request->get('_token'))) {
            $em->remove($ressource);
            $em->flush();
            
            $this->addFlash('success', 'La ressource pédagogique a été supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        
        // Redirection vers la page principale
        return $this->redirectToRoute('admin_cours_index');
    }
}