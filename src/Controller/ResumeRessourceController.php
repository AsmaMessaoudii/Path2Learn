<?php
// src/Controller/ResumeRessourceController.php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\RessourcePedagogique;
use App\Service\ResumeRessourceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/resume-ressource')]
class ResumeRessourceController extends AbstractController
{
    #[Route('/ressource/{id}', name: 'app_resume_ressource')]
    public function ressourceRessource(
        RessourcePedagogique $ressource,
        ResumeRessourceService $resumeRessourceService
    ): Response {
        $resume = $resumeRessourceService->analyserRessource($ressource);
        
        return $this->render('resume_ressource/ressource.html.twig', [
            'ressource' => $ressource,
            'resume' => $resume
        ]);
    }

    #[Route('/cours/{id}', name: 'app_resume_ressources_cours')]
    public function ressourcesCours(
        Cours $cours,
        ResumeRessourceService $resumeRessourceService
    ): Response {
        $ressources = $resumeRessourceService->analyserRessourcesCours($cours);
        $resumeGlobal = $resumeRessourceService->genererResumeGlobal($cours);
        
        return $this->render('resume_ressource/cours.html.twig', [
            'cours' => $cours,
            'ressources' => $ressources,  // Tableau d'objets ResumeRessource
            'resumeGlobal' => $resumeGlobal
        ]);
    }
}