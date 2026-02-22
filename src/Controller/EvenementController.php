<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\ParticipationEvent;
use App\Form\ParticipationType;
use App\Repository\EvenementRepository;
use App\Repository\ParticipationEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EvenementController extends AbstractController
{
    #[Route('/evenements', name: 'evenement_list', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $evenementRepository): Response
    {
        $filters = [
            'q' => $request->query->get('q'),
            'category' => $request->query->get('category'),
            'status' => 'ouvert', // Public usually only sees open events
        ];
        
        $page = $request->query->getInt('page', 1);
        $limit = 6;
        
        $evenements = $evenementRepository->findEventsByFilters($filters, $page, $limit);
        $totalEvents = $evenementRepository->countEventsByFilters($filters);
        $totalPages = ceil($totalEvents / $limit);

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/evenement/{id}', name: 'evenement_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Evenement $evenement, EntityManagerInterface $entityManager, ParticipationEventRepository $participationRepo): Response
    {
        $participation = new ParticipationEvent();
        $participation->setEvenement($evenement);
        
        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification 1 : Événement complet
            if ($evenement->isComplet()) {
                $this->addFlash('error', 'Désolé, cet événement est complet.');
                return $this->redirectToRoute('evenement_show', ['id' => $evenement->getId()]);
            }

            // Vérification 2 : Email déjà inscrit
            $existing = $participationRepo->findOneBy([
                'evenement' => $evenement, 
                'emailParticipant' => $participation->getEmailParticipant()
            ]);
            
            if ($existing) {
                $this->addFlash('error', 'Vous êtes déjà inscrit à cet événement avec cet email.');
                return $this->redirectToRoute('evenement_show', ['id' => $evenement->getId()]);
            }

            $entityManager->persist($participation);
            $entityManager->flush();

             if ($evenement->isComplet()) {
                 $evenement->setStatut('complet');
                 $entityManager->flush();
             }

            $this->addFlash('success', 'Votre inscription a été confirmée !');

            return $this->redirectToRoute('evenement_show', ['id' => $evenement->getId()]);
        }

        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }
}
