<?php
// src/Controller/ResumeController.php

namespace App\Controller;

use App\Entity\Cours;
use App\Service\ResumeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/resume')]
class ResumeController extends AbstractController
{
    #[Route('/cours/{id}', name: 'app_resume_cours')]
    public function resumeCours(Cours $cours, ResumeService $resumeService): Response
    {
        $resume = $resumeService->genererResume($cours);
        
        return $this->render('resume/cours.html.twig', [
            'cours' => $cours,
            'resume' => $resume
        ]);
    }

    #[Route('/cours/{id}/fiche', name: 'app_resume_fiche')]
    public function ficheRevision(Cours $cours, ResumeService $resumeService): Response
    {
        $fiche = $resumeService->genererFicheRevision($cours);
        
        return $this->render('resume/fiche.html.twig', [
            'cours' => $cours,
            'fiche' => $fiche
        ]);
    }

    #[Route('/cours/{id}/telecharger', name: 'app_resume_download')]
    public function telechargerFiche(Cours $cours, ResumeService $resumeService): Response
    {
        $fiche = $resumeService->genererFicheRevision($cours);
        
        // Créer un PDF simple
        $nomFichier = 'fiche_revision_' . $cours->getTitre() . '.html';
        
        return new Response(
            $fiche,
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'attachment; filename="' . $nomFichier . '"'
            ]
        );
    }
    #[Route('/ressource/{id}/view', name: 'app_ressource_view')]
public function viewRessource(int $id, RessourcePedagogiqueRepository $ressourceRepo): Response
{
    $ressource = $ressourceRepo->find($id);
    
    if (!$ressource) {
        throw $this->createNotFoundException('Ressource non trouvée');
    }
    
    // Rediriger vers le lien ou le fichier
    if ($ressource->getUrl()) {
        return $this->redirect($ressource->getUrl());
    } elseif ($ressource->getFileName()) {
        // Rediriger vers le fichier uploadé
        return $this->redirect($ressource->getFileUrl());
    }
    
    throw $this->createNotFoundException('Aucun contenu disponible');
}
}