<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProjetRepository;
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
            // Only show flash for system errors (database, etc.)
            $this->addFlash('error', 'Erreur lors de la création du projet: ' . $e->getMessage());
        }
    }

    // REMOVED: Don't add flash messages for validation errors
    // Symfony will show them automatically in the template via form_errors()

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

    // REMOVED: Don't add flash messages for validation errors

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
    Request $request,
    EntityManagerInterface $em,
    ProjetRepository $projetRepository,
    \Symfony\Component\Mailer\MailerInterface $mailer,
    int $id
): Response
{
    // Find the project by ID
    $projet = $projetRepository->find($id);
    
    if (!$projet) {
        $this->addFlash('error', 'Projet non trouvé (ID: ' . $id . ')');
        return $this->redirectToRoute('portfolio_list');
    }
    
    // Get CSRF token from request
    $submittedToken = $request->request->get('_token');
    
    // Check CSRF token
    if (!$this->isCsrfTokenValid('delete-project-' . $projet->getId(), $submittedToken)) {
        $this->addFlash('error', 'Token CSRF invalide.');
        return $this->redirectToRoute('portfolio_list');
    }
    
    // Get form data
    $studentEmail = $request->request->get('student_email');
    $studentName = $request->request->get('student_name');
    $deleteReason = $request->request->get('delete_reason');
    $deleteDetails = $request->request->get('delete_details');
    
    // DEBUG: Log all form data
    error_log('=== DELETE PROJECT DEBUG ===');
    error_log('Project ID: ' . $projet->getId());
    error_log('Project Title: ' . $projet->getTitreProjet());
    error_log('Student Email: ' . ($studentEmail ?: 'NOT PROVIDED'));
    error_log('Student Name: ' . ($studentName ?: 'NOT PROVIDED'));
    error_log('Delete Reason: ' . ($deleteReason ?: 'NOT PROVIDED'));
    
    try {
        // Store portfolio ID for redirect
        $portfolioId = $projet->getPortfolio()->getId();
        
        // Try to send the email first
        $emailSent = false;
        $emailErrorMessage = null;
        
        if ($studentEmail && !empty($studentEmail)) {
            try {
                error_log('Attempting to send email to: ' . $studentEmail);
                
                $email = (new \Symfony\Component\Mime\Email())
                    ->from('nonoreply167@gmail.com')
                    ->to($studentEmail)
                    ->subject('Suppression de votre projet - Portfolio')
                    ->html($this->renderView('emails/project_deleted.html.twig', [
                        'student_name' => $studentName,
                        'project_title' => $projet->getTitreProjet(),
                        'reason' => $deleteReason,
                        'details' => $deleteDetails,
                        'date' => new \DateTime()
                    ]));
                
                $mailer->send($email);
                $emailSent = true;
                error_log('✅ Email sent successfully to: ' . $studentEmail);
                
            } catch (\Exception $emailException) {
                $emailErrorMessage = $emailException->getMessage();
                error_log('❌ Email failed: ' . $emailErrorMessage);
                error_log('File: ' . $emailException->getFile() . ':' . $emailException->getLine());
                error_log('Trace: ' . $emailException->getTraceAsString());
            }
        } else {
            error_log('❌ No student email provided');
            $emailErrorMessage = 'Aucun email étudiant fourni';
        }
        
        // Now delete the project
        $em->remove($projet);
        $em->flush();
        error_log('✅ Project deleted successfully');
        error_log('=== END DEBUG ===');
        
        // Add flash messages based on email status
        if ($emailSent) {
            $this->addFlash('success', 'Projet supprimé et email envoyé avec succès !');
        } elseif ($studentEmail) {
            $this->addFlash('warning', 'Projet supprimé mais l\'email n\'a pas pu être envoyé. Erreur: ' . $emailErrorMessage);
        } else {
            $this->addFlash('success', 'Projet supprimé avec succès !');
            $this->addFlash('info', 'Aucun email étudiant trouvé pour notification.');
        }
        
    } catch (\Exception $e) {
        error_log('❌ Delete error: ' . $e->getMessage());
        $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
    }
    
    return $this->redirectToRoute('portfolio_list');
}



private function sendDeletionEmail(
    \Symfony\Component\Mailer\MailerInterface $mailer,
    string $studentEmail,
    string $studentName,
    string $projectTitle,
    string $reason,
    ?string $details = null
): void {
    $email = (new \Symfony\Component\Mime\Email())
        ->from('nonoreply167@gmail.com')  // Même email que dans le DSN
        ->to($studentEmail)
        ->subject('Suppression de votre projet - Portfolio')
        ->html($this->renderView('emails/project_deleted.html.twig', [
            'student_name' => $studentName,
            'project_title' => $projectTitle,
            'reason' => $reason,
            'details' => $details,
            'date' => new \DateTime()
        ]));
    
    try {
        $mailer->send($email);
    } catch (\Exception $e) {
        // Log l'erreur pour debug
        error_log('Erreur envoi email: ' . $e->getMessage());
        throw $e; // Pour voir l'erreur
    }
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






#[Route('/test-email-config', name: 'test_email_config')]
public function testEmailConfig(\Symfony\Component\Mailer\MailerInterface $mailer): Response
{
    try {
        // Test 1: Check if mailer is configured
        if (!$mailer) {
            return new Response('❌ Mailer is not configured properly');
        }
        
        // Test 2: Try to send a test email
        $email = (new \Symfony\Component\Mime\Email())
            ->from('nonoreply167@gmail.com')
            ->to('nonoreply167@gmail.com') // Send to yourself for testing
            ->subject('Test Email from Portfolio - ' . date('Y-m-d H:i:s'))
            ->html('<h1>Test Email</h1><p>This is a test email to verify your Symfony mailer configuration.</p>');
        
        $mailer->send($email);
        
        return new Response('✅ Email sent successfully! Check your inbox.');
        
    } catch (\Exception $e) {
        $error = '❌ Email failed: ' . $e->getMessage() . "\n";
        $error .= 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
        $error .= 'Trace: ' . $e->getTraceAsString();
        
        return new Response('<pre>' . $error . '</pre>');
    }
}

}






