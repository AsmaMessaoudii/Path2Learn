<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/evenement')]
class AdminEvenementController extends AbstractController
{
    #[Route('/', name: 'admin_evenement_list', methods: ['GET'])]
    public function list(Request $request, EvenementRepository $evenementRepository): Response
    {
        $filters = [
            'q' => $request->query->get('q'),
            'category' => $request->query->get('category'),
            'status' => $request->query->get('status'),
        ];
        
        $page = $request->query->getInt('page', 1);
        $limit = 5;
        
        $evenements = $evenementRepository->findEventsByFilters($filters, $page, $limit);
        $totalEvents = $evenementRepository->countEventsByFilters($filters);
        $totalPages = ceil($totalEvents / $limit);

        return $this->render('admin_evenement/list.html.twig', [
            'evenements' => $evenements,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalEvents' => $totalEvents,
        ]);
    }

    #[Route('/add', name: 'admin_evenement_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/evenements',
                        $newFilename
                    );
                    $evenement->setImageUrl('/uploads/evenements/'.$newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $entityManager->persist($evenement);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été créé avec succès !');

            return $this->redirectToRoute('admin_evenement_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_evenement/add.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/evenements',
                        $newFilename
                    );
                    $evenement->setImageUrl('/uploads/evenements/'.$newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $evenement->setDateModification(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été modifié avec succès !');

            return $this->redirectToRoute('admin_evenement_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_evenement_delete', methods: ['GET'])]
    public function delete(Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($evenement);
        $entityManager->flush();

        $this->addFlash('success', 'L\'événement a été supprimé avec succès !');

        return $this->redirectToRoute('admin_evenement_list', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/{id}/participants', name: 'admin_evenement_participants', methods: ['GET'])]
    public function participants(Evenement $evenement): Response
    {
        return $this->render('admin_evenement/participants.html.twig', [
            'evenement' => $evenement,
            'participations' => $evenement->getParticipationEvent(),
        ]);
    }
}
