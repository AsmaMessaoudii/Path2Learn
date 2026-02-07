<?php

namespace App\Controller;

use App\Repository\CoursRepository;
use App\Repository\RessourcePedagogiqueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    #[Route('/courses', name: 'app_courses')]
    public function index(
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response
    {
        // Récupérer seulement les cours PUBLIÉS
        $coursPublies = $coursRepository->findBy(['statut' => 'publié']);
        
        // Préparer les données avec les ressources
        $coursesWithResources = [];
        foreach ($coursPublies as $cours) {
            // Récupérer les ressources associées à ce cours
            $ressources = $ressourceRepository->findBy(['cours' => $cours]);
            
            $coursesWithResources[] = [
                'course' => $cours,
                'resources' => $ressources
            ];
        }
        
        // Compter le nombre de cours publiés
        $nombreCoursPublies = count($coursPublies);
        
        // Calculer le total des heures (seulement pour les cours publiés)
        $totalHours = 0;
        foreach ($coursPublies as $cours) {
            $totalHours += $cours->getDuree() ?? 0;
        }
        
        return $this->render('course/index.html.twig', [
            'coursesWithResources' => $coursesWithResources,
            'nombreCoursPublies' => $nombreCoursPublies,
            'totalHours' => $totalHours,
        ]);
    }
    
    #[Route('/courses/{id}', name: 'course_show')]
    public function show(
        int $id,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response
    {
        // Récupérer le cours
        $cours = $coursRepository->find($id);
        
        // Vérifier si le cours existe et est publié
        if (!$cours) {
            throw $this->createNotFoundException('Ce cours n\'existe pas.');
        }
        
        // Vérifier si le cours est publié
        if ($cours->getStatut() !== 'publié') {
            throw $this->createNotFoundException('Ce cours n\'est pas disponible.');
        }
        
        // Récupérer les ressources associées
        $ressources = $ressourceRepository->findBy(['cours' => $cours]);
        
        return $this->render('course/show.html.twig', [
            'course' => $cours,
            'resources' => $ressources,
        ]);
    }
}