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

final class ProjetController extends AbstractController
{
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



}
