<?php

namespace App\Controller;

use App\Repository\CoursRepository;
use App\Repository\RessourcePedagogiqueRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    #[Route('/courses', name: 'app_courses')]
    public function index(
        Request $request,  // Notez le "use" en haut pour Request
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response
    {
        // Récupérer le terme de recherche depuis la requête
        $searchQuery = $request->query->get('q', '');
        
        // Récupérer seulement les cours PUBLIÉS
        $coursPublies = [];
        
        if (!empty($searchQuery)) {
            // Si recherche, utiliser la méthode de recherche
            $coursPublies = $coursRepository->searchPublishedCourses($searchQuery);
        } else {
            // Sinon, tous les cours publiés
            $coursPublies = $coursRepository->findBy(['statut' => 'publié']);
        }
        
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
        
        // Calculer le total des heures
        $totalHours = 0;
        foreach ($coursPublies as $cours) {
            $totalHours += $cours->getDuree() ?? 0;
        }
        
        return $this->render('course/index.html.twig', [
            'coursesWithResources' => $coursesWithResources,
            'nombreCoursPublies' => $nombreCoursPublies,
            'totalHours' => $totalHours,
            'searchQuery' => $searchQuery,
            'hasSearchResults' => !empty($searchQuery),
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
    
    #[Route('/courses/{id}/pdf/cours', name: 'course_pdf')]
    public function generateCoursePdf(
        int $id,
        CoursRepository $coursRepository
    ): Response
    {
        // Récupérer le cours
        $cours = $coursRepository->find($id);
        
        if (!$cours || $cours->getStatut() !== 'publié') {
            throw $this->createNotFoundException('Cours non disponible');
        }
        
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Générer le HTML avec le template existant
        $html = $this->renderView('cours_admin/export_single_cours_pdf.html.twig', [
            'cours' => $cours,
            'date_export' => new \DateTime(),
        ]);
        
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        
        // Définir la taille et l'orientation
        $dompdf->setPaper('A4', 'portrait');
        
        // Rendre le PDF
        $dompdf->render();
        
        // Générer le nom du fichier
        $fileName = sprintf('cours-%s-%s.pdf', 
            $cours->getId(),
            date('Y-m-d')
        );
        
        // Retourner la réponse PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate'
            ]
        );
    }
    
    #[Route('/courses/{id}/pdf/ressources', name: 'course_ressources_pdf')]
    public function generateCourseRessourcesPdf(
        int $id,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response
    {
        // Récupérer le cours
        $cours = $coursRepository->find($id);
        
        if (!$cours || $cours->getStatut() !== 'publié') {
            throw $this->createNotFoundException('Cours non disponible');
        }
        
        // Récupérer les ressources
        $ressources = $ressourceRepository->findBy(['cours' => $cours]);
        
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Vérifier si on utilise le template avancé ou simple
        $template = 'cours_admin/export_ressources_advanced_pdf.html.twig';
        
        // Générer le HTML avec le template existant
        $html = $this->renderView($template, [
            'cours' => $cours,
            'ressources' => $ressources,
            'date_export' => new \DateTime(),
        ]);
        
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Générer le nom du fichier
        $fileName = sprintf('ressources-cours-%s-%s.pdf', 
            $cours->getId(),
            date('Y-m-d')
        );
        
        // Retourner la réponse PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate'
            ]
        );
    }
    
    #[Route('/courses/{id}/ressource/{ressourceId}/pdf', name: 'single_ressource_pdf')]
    public function generateSingleRessourcePdf(
        int $id,
        int $ressourceId,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response
    {
        // Récupérer le cours
        $cours = $coursRepository->find($id);
        
        if (!$cours || $cours->getStatut() !== 'publié') {
            throw $this->createNotFoundException('Cours non disponible');
        }
        
        // Récupérer la ressource
        $ressource = $ressourceRepository->find($ressourceId);
        
        if (!$ressource || $ressource->getCours()->getId() !== $cours->getId()) {
            throw $this->createNotFoundException('Ressource non disponible');
        }
        
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Générer le HTML avec le template existant
        $html = $this->renderView('cours_admin/export_single_ressource_pdf.html.twig', [
            'ressource' => $ressource,
            'date_export' => new \DateTime(),
        ]);
        
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Générer le nom du fichier
        $fileName = sprintf('ressource-%s-%s.pdf', 
            $ressource->getId(),
            date('Y-m-d')
        );
        
        // Retourner la réponse PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate'
            ]
        );
    }
    
    #[Route('/courses/pdf/all', name: 'all_courses_pdf')]
    public function generateAllCoursesPdf(
        CoursRepository $coursRepository
    ): Response
    {
        // Récupérer seulement les cours PUBLIÉS
        $coursPublies = $coursRepository->findBy(['statut' => 'publié']);
        
        if (empty($coursPublies)) {
            throw $this->createNotFoundException('Aucun cours disponible');
        }
        
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Générer le HTML avec le template existant
        $html = $this->renderView('cours_admin/export_pdf.html.twig', [
            'coursList' => $coursPublies,
            'date_export' => new \DateTime(),
        ]);
        
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Générer le nom du fichier
        $fileName = sprintf('catalogue-cours-%s.pdf', date('Y-m-d'));
        
        // Retourner la réponse PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate'
            ]
        );
    }
}