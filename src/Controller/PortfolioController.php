<?php

namespace App\Controller;

use App\Entity\Portfolio;
use App\Form\PortfolioType;
use App\Repository\PortfolioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PortfolioController extends AbstractController
{
    #[Route('/portfolio', name: 'app_portfolio')]
    public function index(PortfolioRepository $portfolioRepository): Response
    {
        $portfolios = $portfolioRepository->findAll();

        return $this->render('portfolio/index.html.twig', [
            'portfolios' => $portfolios,
        ]);
    }
    #[Route('/portfolio/new', name: 'portfolio_newPortfolio')]
public function new(Request $request, EntityManagerInterface $em): Response
{
    $portfolio = new Portfolio();

    $form = $this->createForm(PortfolioType::class, $portfolio);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($portfolio);
        $em->flush();

        return $this->redirectToRoute('app_portfolio');
    }

    return $this->render('portfolio/CreatePortfolio.html.twig', [
        'form' => $form->createView(),
    ]);
}

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

        return $this->redirectToRoute('app_portfolio');
    }

    return $this->render('portfolio/UpdatePortfolio.html.twig', [
        'form' => $form->createView(),
        'portfolio' => $portfolio,
    ]);
}

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
    }

    return $this->redirectToRoute('app_portfolio');
}

}
