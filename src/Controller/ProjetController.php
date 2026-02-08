<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Portfolio;
use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\PortfolioRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Validator\Validator\ValidatorInterface; // AJOUTÉ ICI

final class ProjetController extends AbstractController
{
    #[Route('/home/projet/{id}', name: 'front_projet_show')]
    public function showFront(Projet $projet): Response
    {
        return $this->render('home/projet/HomeShowProjet.html.twig', [
            'projet' => $projet,
        ]);
    }

    #[Route('/projet/{id}', name: 'app_projet')]
    public function show(Projet $projet): Response
    {
        return $this->render('projet/index.html.twig', [
            'projet' => $projet,
        ]);
    }

    #[Route('/project/new/{portfolioId}', name: 'project_new')]
    public function new(
        int $portfolioId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $portfolio = $em->getRepository(Portfolio::class)->find($portfolioId);

        if (!$portfolio) {
            throw $this->createNotFoundException('Portfolio not found');
        }

        $project = new Projet();
        $project->setPortfolio($portfolio);

        $form = $this->createForm(ProjetType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('app_portfolio');
        }

        return $this->render('projet/createProject.html.twig', [
            'form' => $form->createView(),
            'portfolio' => $portfolio,
        ]);
    }

    #[Route('/home/project/new/{portfolioId}', name: 'front_project_new')]
public function newFront(
    int $portfolioId,
    Request $request,
    EntityManagerInterface $em
): Response {
    $portfolio = $em->getRepository(Portfolio::class)->find($portfolioId);

    if (!$portfolio) {
        $this->addFlash('error', 'Portfolio non trouvé');
        return $this->redirectToRoute('home_portfolio');
    }

    $project = new Projet();
    $project->setPortfolio($portfolio);

    $form = $this->createForm(ProjetType::class, $project);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        // Le formulaire n'est PAS valide
        if (!$form->isValid()) {
            // Récupérer toutes les erreurs
            $errors = $form->getErrors(true);
            
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                // Erreurs générales
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
            }
        } else {
            // Le formulaire EST valide
            try {
                // Nettoyage des données
                $project->sanitize();
                
                // Persistance
                $em->persist($project);
                $em->flush();

                $this->addFlash('success', 'Projet créé avec succès !');
                return $this->redirectToRoute('home_portfolio', ['id' => $portfolioId]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création du projet: ' . $e->getMessage());
            }
        }
    }

    return $this->render('home/projet/HomeCreateProject.html.twig', [
        'form' => $form->createView(),
        'portfolio' => $portfolio,
    ]);
}

    #[Route('/home/project/{id}/edit', name: 'front_project_edit')]
    public function editFront(
        Projet $projet,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator // CORRECT
    ): Response {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $errors = $validator->validate($projet);
            
            if (count($errors) === 0 && $form->isValid()) {
                try {
                    $projet->sanitize();
                    $em->flush();

                    $this->addFlash('success', 'Projet modifié avec succès !');
                    return $this->redirectToRoute('front_projet_show', ['id' => $projet->getId()]);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la modification: ' . $e->getMessage());
                }
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('home/projet/HomeUpdateProject.html.twig', [
            'form' => $form->createView(),
            'projet' => $projet,
        ]);
    }

    #[Route('/project/{id}/edit', name: 'project_edit')]
    public function edit(
        Projet $projet,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_projet', [
                'id' => $projet->getId(),
            ]);
        }

        return $this->render('projet/UpdateProject.html.twig', [
            'form' => $form->createView(),
            'projet' => $projet,
        ]);
    }

    #[Route('/front/project/{id}/delete', name: 'front_project_delete', methods: ['POST'])]
    public function deleteFront(
        Projet $projet,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        if ($this->isCsrfTokenValid(
            'delete-project-' . $projet->getId(),
            $request->request->get('_token')
        )) {
            $em->remove($projet);
            $em->flush();
        }

        return $this->redirectToRoute('home_portfolio', ['id' => $projet->getPortfolio()->getId()]);
    }

    #[Route('/project/{id}/delete', name: 'project_delete', methods: ['POST'])]
    public function delete(
        Projet $projet,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        if ($this->isCsrfTokenValid(
            'delete-project-' . $projet->getId(),
            $request->request->get('_token')
        )) {
            $em->remove($projet);
            $em->flush();
        }

        return $this->redirectToRoute('app_portfolio');
    }

    #[Route('/home/projet/{id}/export-pdf', name: 'front_projet_export_pdf')]
    public function exportPdfFront(Projet $projet): Response
    {
        // Increase memory limit for large PDFs
        ini_set('memory_limit', '256M');
        set_time_limit(300); // 5 minutes timeout
        
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        $html = $this->renderView('projet/pdf_export.html.twig', [
            'projet' => $projet,
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        
        try {
            $dompdf->render();
            
            $date = new \DateTime();
            $filename = 'projet_' . $projet->getId() . '_' . $date->format('Y-m-d') . '.pdf';
            
            // Force download
            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public'
            ]);
            
        } catch (\Exception $e) {
            // Log error and show message
            $this->addFlash('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
            return $this->redirectToRoute('front_projet_show', ['id' => $projet->getId()]);
        }
    }

    #[Route('/projet/{id}/export-pdf', name: 'projet_export_pdf')]
    public function exportPdf(Projet $projet): Response
    {
        // Increase memory and timeout limits for PDF generation
        ini_set('memory_limit', '256M');
        set_time_limit(300); // 5 minutes timeout
        
        // Configuration des options PDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        $pdfOptions->set('chroot', $this->getParameter('kernel.project_dir'));
        
        // Instanciation de Dompdf
        $dompdf = new Dompdf($pdfOptions);
        
        try {
            // Rendu du template Twig en HTML
            $html = $this->renderView('projet/pdf_export.html.twig', [
                'projet' => $projet,
            ]);
            
            // Chargement du HTML dans Dompdf
            $dompdf->loadHtml($html);
            
            // Configuration de la taille et orientation du papier
            $dompdf->setPaper('A4', 'portrait');
            
            // Rendu du PDF
            $dompdf->render();
            
            // Nom du fichier avec date
            $date = new \DateTime();
            $filename = 'projet_' . $projet->getId() . '_' . $projet->getTitreProjet() . '_' . $date->format('Y-m-d') . '.pdf';
            
            // Nettoyage du nom de fichier (enlève les caractères spéciaux)
            $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
            
            // Envoi du PDF au navigateur (téléchargement forcé)
            $output = $dompdf->output();
            
            return new Response($output, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($output),
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public'
            ]);
            
        } catch (\Exception $e) {
            // Log l'erreur
            error_log('PDF Export Error: ' . $e->getMessage());
            
            // Retourne une réponse d'erreur
            return new Response(
                'Erreur lors de la génération du PDF: ' . $e->getMessage(),
                500,
                ['Content-Type' => 'text/plain']
            );
        }
    }
}