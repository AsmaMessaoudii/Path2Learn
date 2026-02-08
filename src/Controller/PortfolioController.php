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
    // Front office
#[Route('/home/portfolio', name: 'home_portfolio')]
public function frontIndex(PortfolioRepository $portfolioRepository): Response
{
    $portfolios = $portfolioRepository->findAll();

    return $this->render('home/HomePortfolio.html.twig', [
        'portfolios' => $portfolios,
    ]);
}

    #[Route('/portfolio', name: 'app_portfolio')]
    public function index(PortfolioRepository $portfolioRepository): Response
    {
        $portfolios = $portfolioRepository->findAll();

        return $this->render('portfolio/index.html.twig', [
            'portfolios' => $portfolios,
        ]);
    }
    




#[Route('/home/portfolio/new', name: 'home_portfolio_new')]
public function newFront(Request $request, EntityManagerInterface $em): Response
{
    $portfolio = new Portfolio();

    $form = $this->createForm(PortfolioType::class, $portfolio);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($portfolio);
        $em->flush();

        return $this->redirectToRoute('home_portfolio');
    }

    return $this->render('home/HomeCreatePortfolio.html.twig', [
        'form' => $form->createView(),
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









// Front office
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

        return $this->redirectToRoute('home_portfolio');
    }

    return $this->render('home/HomeUpdatePortfolio.html.twig', [
        'form' => $form->createView(),
        'portfolio' => $portfolio,
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







// Front office
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
    }

    return $this->redirectToRoute('home_portfolio');
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
