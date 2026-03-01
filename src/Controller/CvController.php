<?php

namespace App\Controller;

use App\Entity\CvInfo;
use App\Entity\User;
use App\Form\CvInfoType;
use App\Repository\CvInfoRepository;
use App\Repository\PortfolioRepository;
use App\Repository\ProjetRepository;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CvController extends AbstractController
{
    #[Route('/home/cv', name: 'home_cv')]
    public function index(CvInfoRepository $cvInfoRepository): Response
    {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::STUDENT) {
            $this->addFlash('warning', 'Cette page est réservée aux étudiants.');
            return $this->redirectToRoute('app_login');
        }

        $cvInfo = $cvInfoRepository->findOneByUser($user);

        return $this->render('home/cv/index.html.twig', [
            'cvInfo' => $cvInfo,
        ]);
    }

    #[Route('/home/cv/new', name: 'home_cv_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        CvInfoRepository $cvInfoRepository
    ): Response {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::STUDENT) {
            $this->addFlash('warning', 'Cette page est réservée aux étudiants.');
            return $this->redirectToRoute('app_login');
        }

        // Check if user already has a CV
        $existingCv = $cvInfoRepository->findOneByUser($user);
        if ($existingCv) {
            return $this->redirectToRoute('home_cv_edit', ['id' => $existingCv->getId()]);
        }

        $cvInfo = new CvInfo();
        $cvInfo->setUser($user);

        $form = $this->createForm(CvInfoType::class, $cvInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // The photo is automatically handled by VichUploader
            $em->persist($cvInfo);
            $em->flush();

            $this->addFlash('success', 'CV créé avec succès !');
            return $this->redirectToRoute('home_cv');
        }

        return $this->render('home/cv/form.html.twig', [
            'form' => $form->createView(),
            'cvInfo' => $cvInfo,
            'isNew' => true,
        ]);
    }

#[Route('/home/cv/{id}/edit', name: 'home_cv_edit')]
public function edit(
    CvInfo $cvInfo,
    Request $request,
    EntityManagerInterface $em
): Response {
    $user = $this->getUser();

    if (!$user || $user->getRole() !== UserRole::STUDENT || $cvInfo->getUser() !== $user) {
        $this->addFlash('warning', 'Vous n\'avez pas accès à ce CV.');
        return $this->redirectToRoute('home_cv');
    }

    $this->normalizeCvData($cvInfo);

    $form = $this->createForm(CvInfoType::class, $cvInfo);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // DEBUG: Vérifions si le fichier est présent
        $file = $form->get('photoFile')->getData();
        if ($file) {
            // Afficher dans la barre de debug Symfony
            dump($file->getClientOriginalName());
            dump($file->getSize());
            dump($file->getMimeType());
            
            // Le fichier sera automatiquement traité par VichUploader
        } else {
            dump('Aucun fichier uploadé');
        }

        $cvInfo->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        $this->addFlash('success', 'CV mis à jour avec succès !');
        return $this->redirectToRoute('home_cv');
    }

    return $this->render('home/cv/form.html.twig', [
        'form' => $form->createView(),
        'cvInfo' => $cvInfo,
        'isNew' => false,
    ]);
}

    #[Route('/home/cv/{id}/delete', name: 'home_cv_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        CvInfo $cvInfo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if (!$user || $user->getRole() !== UserRole::STUDENT || $cvInfo->getUser() !== $user) {
            $this->addFlash('warning', 'Vous n\'avez pas accès à ce CV.');
            return $this->redirectToRoute('home_cv');
        }

        if ($this->isCsrfTokenValid('delete-cv-' . $cvInfo->getId(), $request->request->get('_token'))) {
            // Delete photo file if exists
            if ($cvInfo->getPhoto()) {
                $photoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/cv_photos/' . $cvInfo->getPhoto();
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }

            $em->remove($cvInfo);
            $em->flush();
            $this->addFlash('success', 'CV supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('home_cv');
    }

   #[Route('/home/cv/{id}/export-pdf', name: 'home_cv_export_pdf')]
public function exportPdf(
    CvInfo $cvInfo,
    PortfolioRepository $portfolioRepository,
    ProjetRepository $projetRepository
): Response {
    $user = $this->getUser();

    if (!$user || $user->getRole() !== UserRole::STUDENT || $cvInfo->getUser() !== $user) {
        $this->addFlash('warning', 'Vous n\'avez pas accès à ce CV.');
        return $this->redirectToRoute('home_cv');
    }

    // Get user's portfolio and projects
    $portfolio = $portfolioRepository->findOneBy(['user' => $user]);
    $projets = $portfolio ? $projetRepository->findBy(['portfolio' => $portfolio], ['dateRealisation' => 'DESC']) : [];

    // Gérer la photo en base64
    /*$photoBase64 = null;
    if ($cvInfo->getPhoto()) {
        $photoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/cv_photos/' . $cvInfo->getPhoto();
        if (file_exists($photoPath)) {
            $imageData = file_get_contents($photoPath);
            $mimeType = mime_content_type($photoPath);
            $photoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }
    }*/

    // Configure PDF
    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'DejaVu Sans, Arial, sans-serif');
    $pdfOptions->set('isRemoteEnabled', true);
    $pdfOptions->set('isHtml5ParserEnabled', true);
    
    $dompdf = new Dompdf($pdfOptions);
    
    $html = $this->renderView('home/cv/pdf_export.html.twig', [
        'cvInfo' => $cvInfo,
        'user' => $user,
        'portfolio' => $portfolio,
        'projets' => $projets,
        //'photoBase64' => $photoBase64, // Photo en base64
    ]);
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    
    try {
        $dompdf->render();
        
        $filename = 'cv-' . $user->getNom() . '-' . $user->getPrenom() . '-' . date('Y-m-d') . '.pdf';
        $filename = preg_replace('/[^a-zA-Z0-9\-\.]/', '_', $filename);
        
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    } catch (\Exception $e) {
        $this->addFlash('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        return $this->redirectToRoute('home_cv');
    }
}

    /**
     * Normalise les données du CV pour le formulaire
     * Convertit les anciens formats string en format tableau
     */
    private function normalizeCvData(CvInfo $cvInfo): void
    {
        // Normaliser les centres d'intérêt
        $centres = $cvInfo->getCentresInteret();
        if (is_array($centres) && !empty($centres)) {
            // Vérifier si c'est un tableau de strings (ancien format)
            if (is_string($centres[0])) {
                $newCentres = [];
                foreach ($centres as $item) {
                    // Essayer d'extraire l'icône et le nom
                    if (preg_match('/^([\p{So}\p{Sk}])\s*(.+)$/u', $item, $matches)) {
                        $newCentres[] = ['icone' => $matches[1], 'nom' => $matches[2]];
                    } else {
                        $newCentres[] = ['nom' => $item, 'icone' => ''];
                    }
                }
                $cvInfo->setCentresInteret($newCentres);
            }
        }

        // Normaliser les langues
        $langues = $cvInfo->getLangues();
        if (is_array($langues) && !empty($langues)) {
            // Vérifier si c'est un tableau de strings (ancien format)
            if (is_string($langues[0])) {
                $newLangues = [];
                foreach ($langues as $item) {
                    // Essayer de séparer le nom et le niveau (format: "Français - Courant")
                    if (preg_match('/^(.+?)\s*[-–]\s*(.+)$/u', $item, $matches)) {
                        $newLangues[] = ['nom' => trim($matches[1]), 'niveau' => trim($matches[2])];
                    } else {
                        $newLangues[] = ['nom' => $item, 'niveau' => 'Débutant'];
                    }
                }
                $cvInfo->setLangues($newLangues);
            }
        }
    }
}