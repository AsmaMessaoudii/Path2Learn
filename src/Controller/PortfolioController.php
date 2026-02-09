<?php

namespace App\Controller;

use App\Entity\Portfolio;
use App\Form\PortfolioType;
use App\Repository\PortfolioRepository;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

final class PortfolioController extends AbstractController
{
    // Front office avec recherche et tri
    #[Route('/home/portfolio', name: 'home_portfolio')]
    public function frontIndex(
        PortfolioRepository $portfolioRepository, 
        ProjetRepository $projetRepository,
        Request $request
    ): Response
    {
        $portfolios = $portfolioRepository->findAll();
        $portfolio = $portfolios[0] ?? null;
        
        // Récupérer les paramètres de recherche et tri
        $searchTerm = $request->query->get('search', '');
        $sortBy = $request->query->get('sort', 'date_desc');
        $technologie = $request->query->get('technologie', '');
        
        // Initialiser les projets
        $projets = [];
        
        if ($portfolio) {
            // Récupérer les projets avec filtres et tri
            $projets = $projetRepository->findByFilters(
                $portfolio->getId(),
                $searchTerm,
                $sortBy,
                $technologie
            );
        }
        
        // Récupérer toutes les technologies distinctes pour le filtre
        $allTechnologies = $portfolio ? $projetRepository->findDistinctTechnologies($portfolio->getId()) : [];

        return $this->render('home/HomePortfolio.html.twig', [
            'portfolios' => $portfolios,
            'portfolio' => $portfolio,
            'projets' => $projets,
            'searchTerm' => $searchTerm,
            'sortBy' => $sortBy,
            'selectedTechnologie' => $technologie,
            'allTechnologies' => $allTechnologies,
        ]);
    }

    // Admin dashboard avec recherche et tri
    #[Route('/portfolio', name: 'app_portfolio')]
    public function index(
        PortfolioRepository $portfolioRepository, 
        ProjetRepository $projetRepository,
        Request $request
    ): Response
    {
        $portfolios = $portfolioRepository->findAll();
        $portfolio = $portfolios[0] ?? null;
        
        // Récupérer les paramètres de recherche et tri
        $searchTerm = $request->query->get('search', '');
        $sortBy = $request->query->get('sort', 'date_desc');
        $technologie = $request->query->get('technologie', '');
        
        // Initialiser les projets
        $projets = [];
        
        if ($portfolio) {
            // Récupérer les projets avec filtres et tri
            $projets = $projetRepository->findByFilters(
                $portfolio->getId(),
                $searchTerm,
                $sortBy,
                $technologie
            );
        }
        
        // Récupérer toutes les technologies distinctes pour le filtre
        $allTechnologies = $portfolio ? $projetRepository->findDistinctTechnologies($portfolio->getId()) : [];

        return $this->render('portfolio/index.html.twig', [
            'portfolios' => $portfolios,
            'portfolio' => $portfolio,
            'projets' => $projets,
            'searchTerm' => $searchTerm,
            'sortBy' => $sortBy,
            'selectedTechnologie' => $technologie,
            'allTechnologies' => $allTechnologies,
        ]);
    }

    // Front office - Créer un nouveau portfolio
    #[Route('/home/portfolio/new', name: 'home_portfolio_new')]
    public function newFront(Request $request, EntityManagerInterface $em): Response
    {
        $portfolio = new Portfolio();

        $form = $this->createForm(PortfolioType::class, $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Nettoyage des données
                if (method_exists($portfolio, 'sanitize')) {
                    $portfolio->sanitize();
                }
                
                $em->persist($portfolio);
                $em->flush();

                $this->addFlash('success', 'Portfolio créé avec succès !');
                return $this->redirectToRoute('home_portfolio');
            } catch (\Exception $e) {
                // Only show flash for system errors (database, etc.)
                $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
            }
        }

        // REMOVED: Don't add flash messages for validation errors
        // Symfony will show them automatically in the template

        return $this->render('home/HomeCreatePortfolio.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Admin - Créer un nouveau portfolio
    #[Route('/portfolio/new', name: 'portfolio_newPortfolio')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $portfolio = new Portfolio();

        $form = $this->createForm(PortfolioType::class, $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (method_exists($portfolio, 'sanitize')) {
                    $portfolio->sanitize();
                }
                
                $em->persist($portfolio);
                $em->flush();

                $this->addFlash('success', 'Portfolio créé avec succès !');
                return $this->redirectToRoute('app_portfolio');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        // REMOVED: Don't add flash messages for validation errors

        return $this->render('portfolio/CreatePortfolio.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Front office - Modifier un portfolio
    #[Route('/home/portfolio/{id}/edit', name: 'home_portfolio_edit')]
    public function editFront(
        Portfolio $portfolio,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $form = $this->createForm(PortfolioType::class, $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (method_exists($portfolio, 'sanitize')) {
                    $portfolio->sanitize();
                }
                
                $em->flush();
                $this->addFlash('success', 'Portfolio modifié avec succès !');
                return $this->redirectToRoute('home_portfolio');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }

        // REMOVED: Don't add flash messages for validation errors

        return $this->render('home/HomeUpdatePortfolio.html.twig', [
            'form' => $form->createView(),
            'portfolio' => $portfolio,
        ]);
    }

    // Admin - Modifier un portfolio
    #[Route('/portfolio/{id}/edit', name: 'portfolio_edit')]
    public function edit(
        Portfolio $portfolio,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $form = $this->createForm(PortfolioType::class, $portfolio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (method_exists($portfolio, 'sanitize')) {
                    $portfolio->sanitize();
                }
                
                $em->flush();
                $this->addFlash('success', 'Portfolio modifié avec succès !');
                return $this->redirectToRoute('app_portfolio');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        // REMOVED: Don't add flash messages for validation errors

        return $this->render('portfolio/UpdatePortfolio.html.twig', [
            'form' => $form->createView(),
            'portfolio' => $portfolio,
        ]);
    }

    // Front office - Supprimer un portfolio
    #[Route('/home/portfolio/{id}/delete', name: 'home_portfolio_delete', methods: ['POST'])]
    public function frontDelete(
        Request $request,
        Portfolio $portfolio,
        EntityManagerInterface $em
    ): Response
    {
        if ($this->isCsrfTokenValid(
            'delete-portfolio-' . $portfolio->getId(),
            $request->request->get('_token')
        )) {
            try {
                $em->remove($portfolio);
                $em->flush();
                $this->addFlash('success', 'Portfolio supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('home_portfolio');
    }

    // Admin - Supprimer un portfolio
    #[Route('/portfolio/{id}/delete', name: 'portfolio_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Portfolio $portfolio,
        EntityManagerInterface $em
    ): Response
    {
        if ($this->isCsrfTokenValid('delete-portfolio-' . $portfolio->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($portfolio);
                $em->flush();
                $this->addFlash('success', 'Portfolio supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_portfolio');
    }
    
    // Méthode optionnelle pour réinitialiser les filtres
    #[Route('/home/portfolio/reset', name: 'home_portfolio_reset')]
    public function resetFrontFilters(): Response
    {
        return $this->redirectToRoute('home_portfolio');
    }
    
    #[Route('/portfolio/reset', name: 'app_portfolio_reset')]
    public function resetFilters(): Response
    {
        return $this->redirectToRoute('app_portfolio');
    }

    #[Route('/home/portfolio/{id}/export-pdf', name: 'home_portfolio_export_pdf')]
    public function exportPdfFront(Portfolio $portfolio, ProjetRepository $projetRepository): Response
    {
        // Increase memory and timeout limits
        ini_set('memory_limit', '256M');
        set_time_limit(300);
        
        // Get all projects for this portfolio
        $projets = $projetRepository->findBy(['portfolio' => $portfolio], ['dateRealisation' => 'DESC']);
        
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        try {
            $html = $this->renderView('portfolio/pdf_export.html.twig', [
                'portfolio' => $portfolio,
                'projets' => $projets,
            ]);
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $date = new \DateTime();
            $filename = 'portfolio_' . $portfolio->getId() . '_' . $date->format('Y-m-d') . '.pdf';
            
            // Clean filename
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
            error_log('Portfolio PDF Export Error: ' . $e->getMessage());
            
            $this->addFlash('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
            return $this->redirectToRoute('home_portfolio');
        }
    }

    #[Route('/portfolio/{id}/export-pdf', name: 'portfolio_export_pdf')]
    public function exportPdf(int $id, PortfolioRepository $portfolioRepository): Response
    {
        $portfolio = $portfolioRepository->find($id);
        
        if (!$portfolio) {
            throw $this->createNotFoundException('Portfolio non trouvé');
        }

        // Récupérer les projets
        $projets = $portfolio->getProjet();
        
        // Configuration de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'DejaVu Sans, Arial, sans-serif');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Rendu du template
        $html = $this->renderView('portfolio/pdf_export.html.twig', [
            'portfolio' => $portfolio,
            'projets' => $projets,
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        
        try {
            $dompdf->render();
            
            // Envoi du PDF
            $output = $dompdf->output();
            
            return new Response($output, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="portfolio-' . $portfolio->getTitre() . '.pdf"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
            return $this->redirectToRoute('app_portfolio');
        }
    }
}