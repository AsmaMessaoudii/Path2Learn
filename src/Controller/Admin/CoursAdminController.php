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
    #[Route('/', name: 'admin_cours_index')]
    public function index(
        Request $request,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository,
        EntityManagerInterface $em
    ): Response
    {
        // Récupérer toutes les données
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAll();
        
        // Formulaire pour ajouter un cours
        $cours = new Cours();
        $cours->setUser($this->getUser());
        $cours->setDateCreation(new \DateTime());
        
        $formCours = $this->createForm(CoursType::class, $cours);
        $formCours->handleRequest($request);
        
        // Formulaire pour ajouter une ressource
        $ressource = new RessourcePedagogique();
        $ressource->setDateAjout(new \DateTime());
        
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, $ressource);
        $formRessource->handleRequest($request);
        
        // Gérer la soumission du formulaire Cours
        if ($formCours->isSubmitted() && $formCours->isValid()) {
            $em->persist($cours);
            $em->flush();
            
            $this->addFlash('success', 'Le cours a été ajouté avec succès !');
            
            return $this->redirectToRoute('admin_cours_index');
        }
        
        // Gérer la soumission du formulaire Ressource
        if ($formRessource->isSubmitted() && $formRessource->isValid()) {
            $em->persist($ressource);
            $em->flush();
            
            $this->addFlash('success', 'La ressource pédagogique a été ajoutée avec succès !');
            
            return $this->redirectToRoute('admin_cours_index');
        }
        
        return $this->render('cours_admin/index_cours.html.twig', [
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'formCours' => $formCours->createView(),
            'formRessource' => $formRessource->createView(),
        ]);
    }
    
    // PAS de route de suppression des ressources ici !
    // La suppression est gérée par RessourceAdminController
}