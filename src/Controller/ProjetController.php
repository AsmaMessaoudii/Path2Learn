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
use Dompdf\Dompdf;
use Dompdf\Options;

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

        // Check for validation errors BEFORE isValid()
        if ($form->isSubmitted() && !$form->isValid()) {
            // Collect ALL field errors
            $errors = [];
            
            // Check each field individually
            foreach ($form->all() as $child) {
                if ($child->isSubmitted() && !$child->isValid()) {
                    foreach ($child->getErrors() as $error) {
                        $fieldName = $child->getName();
                        $label = $child->getConfig()->getOption('label') ?? $fieldName;
                        
                        // Store errors by field name
                        $errors[$fieldName][] = [
                            'label' => $label,
                            'message' => $error->getMessage()
                        ];
                    }
                }
            }
            
            // Show ALL error messages
            foreach ($errors as $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    $this->addFlash('error', '<strong>' . $error['label'] . ':</strong> ' . $error['message']);
                }
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();

            $this->addFlash('success', 'Projet créé avec succès !');
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

        // Check for ALL validation errors
        if ($form->isSubmitted() && !$form->isValid()) {
            // Get ALL errors from ALL fields
            $allErrors = [];
            
            // Global form errors
            foreach ($form->getErrors() as $error) {
                $allErrors[] = $error->getMessage();
            }
            
            // Field-specific errors
            foreach ($form->all() as $child) {
                $fieldErrors = $child->getErrors();
                if (count($fieldErrors) > 0) {
                    $fieldName = $child->getName();
                    $fieldLabel = $child->getConfig()->getOption('label') ?? $fieldName;
                    
                    foreach ($fieldErrors as $error) {
                        $allErrors[] = '<strong>' . $fieldLabel . ':</strong> ' . $error->getMessage();
                    }
                }
            }
            
            // Show ALL errors as flash messages
            foreach ($allErrors as $errorMsg) {
                $this->addFlash('error', $errorMsg);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Nettoyage des données
                if (method_exists($project, 'sanitize')) {
                    $project->sanitize();
                }
                
                // Persistance
                $em->persist($project);
                $em->flush();

                $this->addFlash('success', 'Projet créé avec succès !');
                return $this->redirectToRoute('home_portfolio', ['id' => $portfolioId]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création du projet: ' . $e->getMessage());
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
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        // Check for ALL validation errors in edit
        if ($form->isSubmitted() && !$form->isValid()) {
            // Collect ALL errors
            $errorMessages = [];
            
            // Check each field
            foreach ($form->all() as $field) {
                $fieldErrors = $field->getErrors();
                if (count($fieldErrors) > 0) {
                    $fieldName = $field->getName();
                    $fieldLabel = $field->getConfig()->getOption('label') ?? $fieldName;
                    
                    foreach ($fieldErrors as $error) {
                        $errorMessages[] = $fieldLabel . ': ' . $error->getMessage();
                    }
                }
            }
            
            // Show ALL errors
            foreach ($errorMessages as $message) {
                $this->addFlash('error', $message);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Nettoyage des données
                if (method_exists($projet, 'sanitize')) {
                    $projet->sanitize();
                }
                
                $em->flush();

                $this->addFlash('success', 'Projet modifié avec succès !');
                return $this->redirectToRoute('front_projet_show', ['id' => $projet->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification: ' . $e->getMessage());
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

        // Check for validation errors
        if ($form->isSubmitted() && !$form->isValid()) {
            // Get ALL errors
            $errors = $form->getErrors(true, false);
            
            // Show each error
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Projet modifié avec succès !');
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
            try {
                $em->remove($projet);
                $em->flush();
                $this->addFlash('success', 'Projet supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
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
            try {
                $em->remove($projet);
                $em->flush();
                $this->addFlash('success', 'Projet supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_portfolio');
    }

    #[Route('/home/projet/{id}/export-pdf', name: 'front_projet_export_pdf')]
    public function exportPdfFront(Projet $projet): Response
    {
        // Increase memory limit for large PDFs
        ini_set('memory_limit', '256M');
        set_time_limit(300);
        
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
            
            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public'
            ]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
            return $this->redirectToRoute('front_projet_show', ['id' => $projet->getId()]);
        }
    }

    #[Route('/projet/{id}/export-pdf', name: 'projet_export_pdf')]
    public function exportPdf(Projet $projet): Response
    {
        ini_set('memory_limit', '256M');
        set_time_limit(300);
        
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        $pdfOptions->set('chroot', $this->getParameter('kernel.project_dir'));
        
        $dompdf = new Dompdf($pdfOptions);
        
        try {
            $html = $this->renderView('projet/pdf_export.html.twig', [
                'projet' => $projet,
            ]);
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $date = new \DateTime();
            $filename = 'projet_' . $projet->getId() . '_' . $projet->getTitreProjet() . '_' . $date->format('Y-m-d') . '.pdf';
            $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
            
            $output = $dompdf->output();
            
            return new Response($output, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($output),
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public'
            ]);
            
        } catch (\Exception $e) {
            error_log('PDF Export Error: ' . $e->getMessage());
            
            return new Response(
                'Erreur lors de la génération du PDF: ' . $e->getMessage(),
                500,
                ['Content-Type' => 'text/plain']
            );
        }
    }
}