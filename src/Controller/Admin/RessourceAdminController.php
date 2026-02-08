<?php

namespace App\Controller\Admin;

use App\Entity\RessourcePedagogique;
use App\Entity\Cours;
use App\Form\RessourcePedagogiqueType;
use App\Form\CoursType;
use App\Repository\RessourcePedagogiqueRepository;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/ressources')]
class RessourceAdminController extends AbstractController
{
    #[Route('/', name: 'admin_ressources_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response
    {
        // Récupérer le paramètre de tri
        $sortBy = $request->query->get('sort', 'titre');
        $direction = $request->query->get('direction', 'ASC');
        
        // Initialisation des variables
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAllSorted($sortBy, $direction);
        
        // ========== GESTION DU FORMULAIRE RESSOURCE ==========
        $ressource = new RessourcePedagogique();
        $ressource->setDateAjout(new \DateTime());
        
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, $ressource, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_ressource',
            'csrf_token_id'   => 'ressource_item',
        ]);
        $formRessource->handleRequest($request);
        
        // Vérifier si c'est le formulaire ressource qui est soumis
        $isRessourceSubmitted = $formRessource->isSubmitted() && $formRessource->getName() === $request->request->get('form_name');
        
        if ($isRessourceSubmitted) {
            if ($formRessource->isValid()) {
                try {
                    // Gestion du fichier uploadé
                    $file = $formRessource->get('file')->getData();
                    
                    if ($file) {
                        // Vérifier la taille du fichier (10MB max)
                        if ($file->getSize() > 10 * 1024 * 1024) {
                            $this->addFlash('error', 'Le fichier est trop volumineux (max 10MB)');
                            return $this->redirectToRoute('admin_ressources_index');
                        }
                        
                        // Vérifier le type MIME
                        $allowedMimeTypes = [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                            'video/mp4',
                            'video/mpeg',
                            'video/quicktime',
                            'audio/mpeg',
                            'audio/wav',
                            'text/plain'
                        ];
                        
                        $mimeType = $file->getMimeType();
                        if (!in_array($mimeType, $allowedMimeTypes)) {
                            $this->addFlash('error', 'Type de fichier non autorisé. Formats acceptés : PDF, Word, Excel, PowerPoint, Images, Vidéos, Audio, Texte.');
                            return $this->redirectToRoute('admin_ressources_index');
                        }
                        
                        // Définir le type de ressource selon le type MIME
                        if (str_starts_with($mimeType, 'image/')) {
                            $ressource->setType('Image');
                        } elseif (str_starts_with($mimeType, 'video/')) {
                            $ressource->setType('Vidéo');
                        } elseif ($mimeType === 'application/pdf') {
                            $ressource->setType('PDF');
                        } elseif (str_starts_with($mimeType, 'audio/')) {
                            $ressource->setType('Audio');
                        } elseif (in_array($mimeType, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
                            $ressource->setType('Document');
                        } elseif (in_array($mimeType, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
                            $ressource->setType('Document');
                        } elseif (in_array($mimeType, ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])) {
                            $ressource->setType('Présentation');
                        }
                        
                        // Le fichier sera automatiquement géré par VichUploader
                        $ressource->setFile($file);
                    } else {
                        // Si pas de fichier mais type "Lien", vérifier qu'il y a une URL
                        if ($ressource->getType() === 'Lien' && empty($ressource->getUrl())) {
                            $this->addFlash('error', 'Pour le type "Lien", une URL est requise');
                            return $this->redirectToRoute('admin_ressources_index');
                        }
                        
                        // Si pas de fichier et pas d'URL, vérifier le type
                        if (empty($ressource->getUrl()) && $ressource->getType() !== 'Exercice') {
                            $this->addFlash('error', 'Veuillez soit uploader un fichier, soit fournir une URL');
                            return $this->redirectToRoute('admin_ressources_index');
                        }
                    }
                    
                    $em->persist($ressource);
                    $em->flush();
                    
                    $this->addFlash('success', 'La ressource pédagogique a été ajoutée avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout de la ressource : ' . $e->getMessage());
                }
            } else {
                $errors = $formRessource->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        // ========== GESTION DU FORMULAIRE COURS ==========
        $cours = new Cours();
        $cours->setDateCreation(new \DateTime());
        
        $formCours = $this->createForm(CoursType::class, $cours, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_cours',
            'csrf_token_id'   => 'cours_item',
        ]);
        $formCours->handleRequest($request);
        
        // Vérifier si c'est le formulaire cours qui est soumis
        $isCoursSubmitted = $formCours->isSubmitted() && $formCours->getName() === $request->request->get('form_name');
        
        if ($isCoursSubmitted) {
            if ($formCours->isValid()) {
                try {
                    $em->persist($cours);
                    $em->flush();
                    
                    $this->addFlash('success', 'Le cours a été ajouté avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout du cours : ' . $e->getMessage());
                }
            } else {
                $errors = $formCours->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        return $this->render('cours_admin/index_cours.html.twig', [
            'formRessource' => $formRessource->createView(),
            'formCours' => $formCours->createView(),
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'selectedCours' => null,
            'editForm' => null,
            'selectedRessource' => null,
            'editRessourceForm' => null,
            'active_tab' => 'ressources',
            'current_sort' => ['field' => $sortBy, 'direction' => $direction],
            'searchTerm' => $request->query->get('q', ''),
        ]);
    }

    #[Route('/view/{id}', name: 'admin_ressources_view', methods: ['GET', 'POST'])]
    public function view(
        RessourcePedagogique $ressource,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository,
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        $sortBy = $request->query->get('sort', 'titre');
        $direction = $request->query->get('direction', 'ASC');
        
        // Récupérer toutes les données
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAllSorted($sortBy, $direction);
        
        // ========== GESTION DU FORMULAIRE RESSOURCE ==========
        $newRessource = new RessourcePedagogique();
        $newRessource->setDateAjout(new \DateTime());
        
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, $newRessource, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_ressource',
            'csrf_token_id'   => 'ressource_item',
        ]);
        $formRessource->handleRequest($request);
        
        $isRessourceSubmitted = $formRessource->isSubmitted() && $formRessource->getName() === $request->request->get('form_name');
        
        if ($isRessourceSubmitted) {
            if ($formRessource->isValid()) {
                try {
                    // Gestion du fichier uploadé
                    $file = $formRessource->get('file')->getData();
                    
                    if ($file) {
                        // Vérifier la taille du fichier (10MB max)
                        if ($file->getSize() > 10 * 1024 * 1024) {
                            $this->addFlash('error', 'Le fichier est trop volumineux (max 10MB)');
                            return $this->redirectToRoute('admin_ressources_index');
                        }
                        
                        // Vérifier le type MIME
                        $allowedMimeTypes = [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                            'video/mp4',
                            'video/mpeg',
                            'video/quicktime',
                            'audio/mpeg',
                            'audio/wav',
                            'text/plain'
                        ];
                        
                        $mimeType = $file->getMimeType();
                        if (!in_array($mimeType, $allowedMimeTypes)) {
                            $this->addFlash('error', 'Type de fichier non autorisé. Formats acceptés : PDF, Word, Excel, PowerPoint, Images, Vidéos, Audio, Texte.');
                            return $this->redirectToRoute('admin_ressources_index');
                        }
                        
                        // Définir le type de ressource selon le type MIME
                        if (str_starts_with($mimeType, 'image/')) {
                            $newRessource->setType('Image');
                        } elseif (str_starts_with($mimeType, 'video/')) {
                            $newRessource->setType('Vidéo');
                        } elseif ($mimeType === 'application/pdf') {
                            $newRessource->setType('PDF');
                        } elseif (str_starts_with($mimeType, 'audio/')) {
                            $newRessource->setType('Audio');
                        }
                        
                        // Le fichier sera automatiquement géré par VichUploader
                        $newRessource->setFile($file);
                    }
                    
                    $em->persist($newRessource);
                    $em->flush();
                    
                    $this->addFlash('success', 'La ressource pédagogique a été ajoutée avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout de la ressource : ' . $e->getMessage());
                }
            } else {
                $errors = $formRessource->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        // ========== GESTION DU FORMULAIRE COURS ==========
        $cours = new Cours();
        $cours->setDateCreation(new \DateTime());
        
        $formCours = $this->createForm(CoursType::class, $cours, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_cours',
            'csrf_token_id'   => 'cours_item',
        ]);
        $formCours->handleRequest($request);
        
        $isCoursSubmitted = $formCours->isSubmitted() && $formCours->getName() === $request->request->get('form_name');
        
        if ($isCoursSubmitted) {
            if ($formCours->isValid()) {
                try {
                    $em->persist($cours);
                    $em->flush();
                    
                    $this->addFlash('success', 'Le cours a été ajouté avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout du cours : ' . $e->getMessage());
                }
            } else {
                $errors = $formCours->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        return $this->render('cours_admin/index_cours.html.twig', [
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'formRessource' => $formRessource->createView(),
            'formCours' => $formCours->createView(),
            'selectedCours' => null,
            'editForm' => null,
            'selectedRessource' => $ressource,
            'editRessourceForm' => null,
            'active_tab' => 'ressources',
            'current_sort' => ['field' => $sortBy, 'direction' => $direction],
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_ressources_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        RessourcePedagogique $ressource,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository,
        EntityManagerInterface $em
    ): Response
    {
        $sortBy = $request->query->get('sort', 'titre');
        $direction = $request->query->get('direction', 'ASC');
        
        // Créer le formulaire d'édition
        $editRessourceForm = $this->createForm(RessourcePedagogiqueType::class, $ressource, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_edit_ressource',
            'csrf_token_id'   => 'edit_ressource_item',
        ]);
        $editRessourceForm->handleRequest($request);
        
        // Si le formulaire d'édition est soumis
        if ($editRessourceForm->isSubmitted() && $editRessourceForm->isValid()) {
            try {
                // Gestion du fichier uploadé
                $file = $editRessourceForm->get('file')->getData();
                
                if ($file) {
                    // Vérifier la taille du fichier (10MB max)
                    if ($file->getSize() > 10 * 1024 * 1024) {
                        $this->addFlash('error', 'Le fichier est trop volumineux (max 10MB)');
                        return $this->redirectToRoute('admin_ressources_edit', ['id' => $ressource->getId()]);
                    }
                    
                    // Vérifier le type MIME
                    $allowedMimeTypes = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/webp',
                        'video/mp4',
                        'video/mpeg',
                        'video/quicktime',
                        'audio/mpeg',
                        'audio/wav',
                        'text/plain'
                    ];
                    
                    $mimeType = $file->getMimeType();
                    if (!in_array($mimeType, $allowedMimeTypes)) {
                        $this->addFlash('error', 'Type de fichier non autorisé. Formats acceptés : PDF, Word, Excel, PowerPoint, Images, Vidéos, Audio, Texte.');
                        return $this->redirectToRoute('admin_ressources_edit', ['id' => $ressource->getId()]);
                    }
                    
                    // Définir le type de ressource selon le type MIME
                    if (str_starts_with($mimeType, 'image/')) {
                        $ressource->setType('Image');
                    } elseif (str_starts_with($mimeType, 'video/')) {
                        $ressource->setType('Vidéo');
                    } elseif ($mimeType === 'application/pdf') {
                        $ressource->setType('PDF');
                    } elseif (str_starts_with($mimeType, 'audio/')) {
                        $ressource->setType('Audio');
                    }
                    
                    // Le fichier sera automatiquement géré par VichUploader
                    $ressource->setFile($file);
                }
                
                $em->flush();
                $this->addFlash('success', 'La ressource pédagogique a été modifiée avec succès !');
                return $this->redirectToRoute('admin_ressources_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        } elseif ($editRessourceForm->isSubmitted() && !$editRessourceForm->isValid()) {
            $errors = $editRessourceForm->getErrors(true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        
        // Récupérer toutes les données
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAllSorted($sortBy, $direction);
        
        // ========== GESTION DU FORMULAIRE RESSOURCE ==========
        $newRessource = new RessourcePedagogique();
        $newRessource->setDateAjout(new \DateTime());
        
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, $newRessource, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_ressource',
            'csrf_token_id'   => 'ressource_item',
        ]);
        $formRessource->handleRequest($request);
        
        $isRessourceSubmitted = $formRessource->isSubmitted() && $formRessource->getName() === $request->request->get('form_name');
        
        if ($isRessourceSubmitted) {
            if ($formRessource->isValid()) {
                try {
                    // Gestion du fichier uploadé
                    $file = $formRessource->get('file')->getData();
                    
                    if ($file) {
                        // Vérifier la taille du fichier (10MB max)
                        if ($file->getSize() > 10 * 1024 * 1024) {
                            $this->addFlash('error', 'Le fichier est trop volumineux (max 10MB)');
                            return $this->redirectToRoute('admin_ressources_index');
                        }
                        
                        // Vérifier le type MIME
                        $allowedMimeTypes = [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                            'video/mp4',
                            'video/mpeg',
                            'video/quicktime',
                            'audio/mpeg',
                            'audio/wav',
                            'text/plain'
                        ];
                        
                        $mimeType = $file->getMimeType();
                        if (!in_array($mimeType, $allowedMimeTypes)) {
                            $this->addFlash('error', 'Type de fichier non autorisé. Formats acceptés : PDF, Word, Excel, PowerPoint, Images, Vidéos, Audio, Texte.');
                            return $this->redirectToRoute('admin_ressources_index');
                        }
                        
                        // Définir le type de ressource selon le type MIME
                        if (str_starts_with($mimeType, 'image/')) {
                            $newRessource->setType('Image');
                        } elseif (str_starts_with($mimeType, 'video/')) {
                            $newRessource->setType('Vidéo');
                        } elseif ($mimeType === 'application/pdf') {
                            $newRessource->setType('PDF');
                        } elseif (str_starts_with($mimeType, 'audio/')) {
                            $newRessource->setType('Audio');
                        }
                        
                        // Le fichier sera automatiquement géré par VichUploader
                        $newRessource->setFile($file);
                    }
                    
                    $em->persist($newRessource);
                    $em->flush();
                    
                    $this->addFlash('success', 'La ressource pédagogique a été ajoutée avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout de la ressource : ' . $e->getMessage());
                }
            } else {
                $errors = $formRessource->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        // ========== GESTION DU FORMULAIRE COURS ==========
        $cours = new Cours();
        $cours->setDateCreation(new \DateTime());
        
        $formCours = $this->createForm(CoursType::class, $cours, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token_cours',
            'csrf_token_id'   => 'cours_item',
        ]);
        $formCours->handleRequest($request);
        
        $isCoursSubmitted = $formCours->isSubmitted() && $formCours->getName() === $request->request->get('form_name');
        
        if ($isCoursSubmitted) {
            if ($formCours->isValid()) {
                try {
                    $em->persist($cours);
                    $em->flush();
                    
                    $this->addFlash('success', 'Le cours a été ajouté avec succès !');
                    return $this->redirectToRoute('admin_ressources_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'ajout du cours : ' . $e->getMessage());
                }
            } else {
                $errors = $formCours->getErrors(true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        return $this->render('cours_admin/index_cours.html.twig', [
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'formRessource' => $formRessource->createView(),
            'formCours' => $formCours->createView(),
            'selectedCours' => null,
            'editForm' => null,
            'selectedRessource' => $ressource,
            'editRessourceForm' => $editRessourceForm->createView(),
            'active_tab' => 'ressources',
            'current_sort' => ['field' => $sortBy, 'direction' => $direction],
        ]);
    }

    #[Route('/{id}', name: 'admin_ressources_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        RessourcePedagogique $ressource, 
        EntityManagerInterface $em
    ): Response
    {
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('delete_ressource_' . $ressource->getId(), $token)) {
            try {
                // Supprimer le fichier physique s'il existe
                if ($ressource->getFileName()) {
                    $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/ressources/' . $ressource->getFileName();
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                $em->remove($ressource);
                $em->flush();
                $this->addFlash('success', 'La ressource pédagogique a été supprimée avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
        }
        
        return $this->redirectToRoute('admin_ressources_index');
    }

    #[Route('/tri/{sortBy}/{direction}', name: 'admin_ressources_tri', methods: ['GET'])]
    public function tri(
        string $sortBy = 'titre',
        string $direction = 'ASC',
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        $coursList = $coursRepository->findAll();
        $ressourcesList = $ressourceRepository->findAllSorted($sortBy, $direction);

        // Créer des formulaires vides
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, new RessourcePedagogique());
        $formCours = $this->createForm(CoursType::class, new Cours());

        return $this->render('cours_admin/index_cours.html.twig', [
            'formRessource' => $formRessource->createView(),
            'formCours' => $formCours->createView(),
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'selectedCours' => null,
            'editForm' => null,
            'selectedRessource' => null,
            'editRessourceForm' => null,
            'active_tab' => 'ressources',
            'current_sort' => ['field' => $sortBy, 'direction' => $direction],
        ]);
    }

    #[Route('/recherche', name: 'admin_ressources_recherche', methods: ['GET'])]
    public function recherche(
        Request $request,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        $searchTerm = $request->query->get('q', '');
        $coursList = [];
        $ressourcesList = [];

        if (!empty($searchTerm)) {
            $coursList = $coursRepository->search($searchTerm);
            $ressourcesList = $ressourceRepository->search($searchTerm);
        } else {
            $coursList = $coursRepository->findAll();
            $ressourcesList = $ressourceRepository->findAll();
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('cours_admin/_recherche_results.html.twig', [
                'coursList' => $coursList,
                'ressourcesList' => $ressourcesList,
                'searchTerm' => $searchTerm,
                'active_tab' => 'ressources',
            ]);
        }

        // Créer des formulaires vides
        $formRessource = $this->createForm(RessourcePedagogiqueType::class, new RessourcePedagogique());
        $formCours = $this->createForm(CoursType::class, new Cours());

        return $this->render('cours_admin/index_cours.html.twig', [
            'formRessource' => $formRessource->createView(),
            'formCours' => $formCours->createView(),
            'coursList' => $coursList,
            'ressourcesList' => $ressourcesList,
            'selectedCours' => null,
            'editForm' => null,
            'selectedRessource' => null,
            'editRessourceForm' => null,
            'active_tab' => 'ressources',
            'searchTerm' => $searchTerm,
            'current_sort' => ['field' => 'titre', 'direction' => 'ASC'],
        ]);
    }

    #[Route('/export/pdf', name: 'admin_ressources_export_pdf', methods: ['GET'])]
    public function exportPdf(
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        $ressourcesList = $ressourceRepository->findAll();
        
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);
        
        // Instantiate Dompdf
        $dompdf = new Dompdf($pdfOptions);
        
        // Retrieve the HTML
        $html = $this->renderView('cours_admin/export_ressources_pdf.html.twig', [
            'ressourcesList' => $ressourcesList,
            'date_export' => new \DateTime(),
        ]);
        
        // Load HTML
        $dompdf->loadHtml($html);
        
        // Setup paper
        $dompdf->setPaper('A4', 'portrait');
        
        // Render
        $dompdf->render();
        
        // Output
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="ressources_export_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }

    #[Route('/export/pdf/advanced', name: 'admin_ressources_export_pdf_advanced', methods: ['GET'])]
    public function exportPdfAdvanced(
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        $ressourcesList = $ressourceRepository->findAll();
        
        // Déterminer le chemin de wkhtmltopdf en fonction du système d'exploitation
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if ($isWindows) {
            // Chemin Windows - ajustez selon votre installation
            $wkhtmltopdfPath = 'C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe';
            
            // Si le chemin n'existe pas, essayez d'autres chemins communs
            if (!file_exists($wkhtmltopdfPath)) {
                $wkhtmltopdfPath = 'C:\\wkhtmltopdf\\bin\\wkhtmltopdf.exe';
            }
            
            // Si toujours pas trouvé, utilisez Dompdf comme fallback
            if (!file_exists($wkhtmltopdfPath)) {
                return $this->exportPdf($ressourceRepository);
            }
        } else {
            // Chemin Linux/Unix
            $wkhtmltopdfPath = '/usr/local/bin/wkhtmltopdf';
        }
        
        try {
            // Créer l'instance PDF avec le bon chemin
            $pdf = new Pdf($wkhtmltopdfPath);
            
            // Configurer les options
            $pdf->setOption('margin-bottom', '10mm');
            $pdf->setOption('margin-left', '10mm');
            $pdf->setOption('margin-right', '10mm');
            $pdf->setOption('margin-top', '10mm');
            $pdf->setOption('page-size', 'A4');
            $pdf->setOption('encoding', 'utf-8');
            $pdf->setOption('enable-javascript', true);
            $pdf->setOption('javascript-delay', 1000);
            $pdf->setOption('no-stop-slow-scripts', true);
            
            // Générer le contenu HTML
            $html = $this->renderView('cours_admin/export_ressources_advanced_pdf.html.twig', [
                'ressourcesList' => $ressourcesList,
                'date_export' => new \DateTime(),
            ]);
            
            // Générer le header HTML
            $headerHtml = $this->renderView('cours_admin/_pdf_header.html.twig', [
                'title' => 'Export des Ressources Pédagogiques',
                'date_export' => new \DateTime(),
            ]);
            
            // Générer le footer HTML
            $footerHtml = $this->renderView('cours_admin/_pdf_footer.html.twig', [
                'date_export' => new \DateTime(),
            ]);
            
            // Ajouter header et footer
            $pdf->setOption('header-html', $headerHtml);
            $pdf->setOption('footer-html', $footerHtml);
            
            // Générer le PDF
            $pdfContent = $pdf->getOutputFromHtml($html);
            
            // Retourner la réponse
            return new Response(
                $pdfContent,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="ressources_avance_' . date('Y-m-d_H-i') . '.pdf"',
                ]
            );
            
        } catch (\Exception $e) {
            // En cas d'erreur avec wkhtmltopdf, fallback sur Dompdf
            $this->addFlash('warning', 'Wkhtmltopdf non disponible, utilisation de Dompdf : ' . $e->getMessage());
            return $this->exportPdf($ressourceRepository);
        }
    }

    #[Route('/export/excel', name: 'admin_ressources_export_excel', methods: ['GET'])]
    public function exportExcel(
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        $ressourcesList = $ressourceRepository->findAll();
        
        // Créer le contenu CSV/Excel
        $csvData = [];
        
        // En-têtes
        $csvData[] = ['ID', 'Titre', 'Type', 'URL', 'Fichier', 'Cours associé', 'Date d\'ajout'];
        
        // Données
        foreach ($ressourcesList as $ressource) {
            $csvData[] = [
                $ressource->getId(),
                $ressource->getTitre(),
                $ressource->getType(),
                $ressource->getUrl() ?? '',
                $ressource->getFileName() ?? '',
                $ressource->getCours() ? $ressource->getCours()->getTitre() : 'Non associé',
                $ressource->getDateAjout()->format('d/m/Y H:i'),
            ];
        }
        
        // Convertir en CSV
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(';', array_map(function($item) {
                // Échapper les guillemets
                $item = str_replace('"', '""', $item);
                // Ajouter des guillemets si nécessaire
                if (strpos($item, ';') !== false || strpos($item, '"') !== false) {
                    $item = '"' . $item . '"';
                }
                return $item;
            }, $row)) . "\n";
        }
        
        // Retourner la réponse
        return new Response(
            $csvContent,
            200,
            [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="ressources_export_' . date('Y-m-d') . '.csv"',
            ]
        );
    }

    #[Route('/statistiques', name: 'admin_ressources_statistiques', methods: ['GET'])]
    public function statistiques(
        RessourcePedagogiqueRepository $ressourceRepository,
        CoursRepository $coursRepository
    ): Response {
        // Récupérer les statistiques
        $totalRessources = $ressourceRepository->count([]);
        $ressourcesParType = $ressourceRepository->countByType();
        $ressourcesParCours = $ressourceRepository->countByCours();
        
        // Dernières ressources ajoutées
        $dernieresRessources = $ressourceRepository->findBy([], ['dateAjout' => 'DESC'], 5);
        
        // Cours avec le plus de ressources
        $coursAvecPlusDeRessources = $ressourceRepository->findCoursWithMostResources(5);
        
        return $this->render('cours_admin/statistiques.html.twig', [
            'totalRessources' => $totalRessources,
            'ressourcesParType' => $ressourcesParType,
            'ressourcesParCours' => $ressourcesParCours,
            'dernieresRessources' => $dernieresRessources,
            'coursAvecPlusDeRessources' => $coursAvecPlusDeRessources,
            'active_tab' => 'statistiques',
        ]);
    }

    #[Route('/batch/delete', name: 'admin_ressources_batch_delete', methods: ['POST'])]
    public function batchDelete(
        Request $request,
        EntityManagerInterface $em,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        $ressourceIds = $request->request->get('ressource_ids', []);
        $token = $request->request->get('_token');
        
        if (!$this->isCsrfTokenValid('batch_delete', $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_ressources_index');
        }
        
        if (empty($ressourceIds)) {
            $this->addFlash('warning', 'Aucune ressource sélectionnée.');
            return $this->redirectToRoute('admin_ressources_index');
        }
        
        $deletedCount = 0;
        foreach ($ressourceIds as $id) {
            $ressource = $ressourceRepository->find($id);
            if ($ressource) {
                try {
                    // Supprimer le fichier physique s'il existe
                    if ($ressource->getFileName()) {
                        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/ressources/' . $ressource->getFileName();
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                    
                    $em->remove($ressource);
                    $deletedCount++;
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la suppression de la ressource ID ' . $id . ': ' . $e->getMessage());
                }
            }
        }
        
        if ($deletedCount > 0) {
            $em->flush();
            $this->addFlash('success', $deletedCount . ' ressource(s) supprimée(s) avec succès.');
        }
        
        return $this->redirectToRoute('admin_ressources_index');
    }

    #[Route('/import', name: 'admin_ressources_import', methods: ['GET', 'POST'])]
    public function import(
        Request $request,
        EntityManagerInterface $em,
        CoursRepository $coursRepository
    ): Response {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('import_file');
            
            if (!$file) {
                $this->addFlash('error', 'Veuillez sélectionner un fichier.');
                return $this->redirectToRoute('admin_ressources_import');
            }
            
            // Vérifier l'extension
            $extension = $file->getClientOriginalExtension();
            if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
                $this->addFlash('error', 'Format de fichier non supporté. Utilisez CSV, XLSX ou XLS.');
                return $this->redirectToRoute('admin_ressources_import');
            }
            
            try {
                // Lire le fichier CSV
                $filePath = $file->getRealPath();
                $handle = fopen($filePath, 'r');
                $importedCount = 0;
                $errors = [];
                
                // Ignorer l'en-tête (première ligne)
                fgetcsv($handle, 1000, ';');
                
                while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                    if (count($data) < 5) {
                        $errors[] = 'Ligne invalide : ' . implode(';', $data);
                        continue;
                    }
                    
                    // Créer la ressource
                    $ressource = new RessourcePedagogique();
                    $ressource->setTitre($data[0]);
                    $ressource->setType($data[1]);
                    $ressource->setUrl($data[2]);
                    $ressource->setDateAjout(new \DateTime());
                    
                    // Associer le cours si spécifié
                    if (!empty($data[4])) {
                        $cours = $coursRepository->findOneBy(['titre' => $data[4]]);
                        if ($cours) {
                            $ressource->setCours($cours);
                        } else {
                            $errors[] = 'Cours non trouvé : ' . $data[4];
                        }
                    }
                    
                    // Valider l'entité
                    $validator = $this->container->get('validator');
                    $violations = $validator->validate($ressource);
                    
                    if (count($violations) === 0) {
                        $em->persist($ressource);
                        $importedCount++;
                    } else {
                        $errors[] = 'Erreurs de validation pour : ' . $data[0];
                    }
                }
                
                fclose($handle);
                
                // Appliquer les changements
                if ($importedCount > 0) {
                    $em->flush();
                    $this->addFlash('success', $importedCount . ' ressource(s) importée(s) avec succès.');
                }
                
                if (!empty($errors)) {
                    $this->addFlash('warning', 'Certaines lignes n\'ont pas pu être importées : ' . implode(', ', array_slice($errors, 0, 5)));
                }
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
            }
            
            return $this->redirectToRoute('admin_ressources_index');
        }
        
        return $this->render('cours_admin/import.html.twig', [
            'active_tab' => 'import',
        ]);
    }

    #[Route('/template/download', name: 'admin_ressources_template_download', methods: ['GET'])]
    public function downloadTemplate(): Response
    {
        // Créer le contenu du template
        $templateContent = "Titre;Type;URL;Description;Cours associé\n";
        $templateContent .= "Exemple de titre;PDF;https://example.com;Description de la ressource;Titre du cours\n";
        $templateContent .= "Autre ressource;Lien;https://autre.com;Autre description;Autre cours\n";
        
        return new Response(
            $templateContent,
            200,
            [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="template_import_ressources.csv"',
            ]
        );
    }

    #[Route('/toggle/{id}', name: 'admin_ressources_toggle_visibility', methods: ['POST'])]
    public function toggleVisibility(
        RessourcePedagogique $ressource,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $token = $request->request->get('_token');
        
        if (!$this->isCsrfTokenValid('toggle_visibility_' . $ressource->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_ressources_index');
        }
        
        try {
            // Basculer la visibilité (si vous avez un champ isVisible)
            // Ou mettre à jour un autre statut
            $ressource->setDateAjout(new \DateTime()); // Exemple
            $em->flush();
            
            $this->addFlash('success', 'Statut de la ressource mis à jour.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('admin_ressources_index');
    }

    #[Route('/duplicate/{id}', name: 'admin_ressources_duplicate', methods: ['POST'])]
    public function duplicate(
        RessourcePedagogique $ressource,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $token = $request->request->get('_token');
        
        if (!$this->isCsrfTokenValid('duplicate_' . $ressource->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_ressources_index');
        }
        
        try {
            // Créer une copie
            $duplicate = clone $ressource;
            $duplicate->setTitre($ressource->getTitre() . ' (copie)');
            $duplicate->setDateAjout(new \DateTime());
            $duplicate->setFileName(null); // Ne pas copier le nom de fichier
            $duplicate->setFile(null); // Ne pas copier le fichier
            
            $em->persist($duplicate);
            $em->flush();
            
            $this->addFlash('success', 'Ressource dupliquée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la duplication : ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('admin_ressources_index');
    }

    #[Route('/export/pdf/{id}', name: 'admin_ressources_export_pdf_single', methods: ['GET'])]
    public function exportPdfSingle(
        RessourcePedagogique $ressource,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response {
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);
        
        // Instantiate Dompdf
        $dompdf = new Dompdf($pdfOptions);
        
        // Retrieve the HTML
        $html = $this->renderView('cours_admin/export_single_ressource_pdf.html.twig', [
            'ressource' => $ressource,
            'date_export' => new \DateTime(),
        ]);
        
        // Load HTML
        $dompdf->loadHtml($html);
        
        // Setup paper
        $dompdf->setPaper('A4', 'portrait');
        
        // Render
        $dompdf->render();
        
        // Output
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="ressource_' . $ressource->getId() . '_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }
    
    #[Route('/download/{id}', name: 'admin_ressources_download', methods: ['GET'])]
    public function download(
        RessourcePedagogique $ressource
    ): Response {
        if (!$ressource->getFileName()) {
            $this->addFlash('error', 'Cette ressource ne contient pas de fichier à télécharger.');
            return $this->redirectToRoute('admin_ressources_index');
        }
        
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/ressources/' . $ressource->getFileName();
        
        if (!file_exists($filePath)) {
            $this->addFlash('error', 'Le fichier n\'existe pas sur le serveur.');
            return $this->redirectToRoute('admin_ressources_index');
        }
        
        return $this->file($filePath, $ressource->getTitre() . '.' . pathinfo($ressource->getFileName(), PATHINFO_EXTENSION));
    }
}