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
            $em->persist($portfolio);
            $em->flush();

            $this->addFlash('success', 'Portfolio créé avec succès !');

            return $this->redirectToRoute('home_portfolio');
        }

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
            $em->persist($portfolio);
            $em->flush();

            $this->addFlash('success', 'Portfolio créé avec succès !');

            return $this->redirectToRoute('app_portfolio');
        }

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
            $em->flush();

            $this->addFlash('success', 'Portfolio modifié avec succès !');

            return $this->redirectToRoute('home_portfolio');
        }

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
            $em->flush();

            $this->addFlash('success', 'Portfolio modifié avec succès !');

            return $this->redirectToRoute('app_portfolio');
        }

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
            $em->remove($portfolio);
            $em->flush();

            $this->addFlash('success', 'Portfolio supprimé avec succès !');
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
            $em->remove($portfolio);
            $em->flush();

            $this->addFlash('success', 'Portfolio supprimé avec succès !');
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
}