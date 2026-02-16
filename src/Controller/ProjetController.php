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



/*
#[Route('/project/{id}/delete', name: 'project_delete', methods: ['POST'])]
public function delete(
    Request $request,
    EntityManagerInterface $em,
    ProjetRepository $projetRepository,
    \Symfony\Component\Mailer\MailerInterface $mailer,
    int $id
): Response
{
    // LOGGING: Start
    error_log('========== DELETE METHOD STARTED ==========');
    error_log('Project ID: ' . $id);
    error_log('Request Method: ' . $request->getMethod());
    
    // Find the project by ID
    $projet = $projetRepository->find($id);
    
    if (!$projet) {
        error_log('ERROR: Project not found with ID: ' . $id);
        $this->addFlash('error', 'Projet non trouvé (ID: ' . $id . ')');
        return $this->redirectToRoute('portfolio_list');
    }
    
    error_log('Project found: ' . $projet->getTitreProjet());
    
    // Get CSRF token from request
    $submittedToken = $request->request->get('_token');
    error_log('CSRF Token submitted: ' . $submittedToken);
    
    // Check CSRF token
    if (!$this->isCsrfTokenValid('delete-project-' . $projet->getId(), $submittedToken)) {
        error_log('ERROR: Invalid CSRF token');
        $this->addFlash('error', 'Token CSRF invalide.');
        return $this->redirectToRoute('portfolio_list');
    }
    
    error_log('CSRF token valid');
    
    // Get form data
    $studentEmail = $request->request->get('student_email', '');
    $studentName = $request->request->get('student_name', '');
    $deleteReason = $request->request->get('delete_reason', '');
    $deleteDetails = $request->request->get('delete_details', '');
    
    error_log('Form data received:');
    error_log('- studentEmail: ' . ($studentEmail ?: 'EMPTY'));
    error_log('- studentName: ' . ($studentName ?: 'EMPTY'));
    error_log('- deleteReason: ' . ($deleteReason ?: 'EMPTY'));
    error_log('- deleteDetails: ' . ($deleteDetails ?: 'EMPTY'));
    
    // Store portfolio ID for redirect
    $portfolioId = $projet->getPortfolio()->getId();
    error_log('Portfolio ID: ' . $portfolioId);
    
    // Try to send the email first
    $emailSent = false;
    $emailErrorMessage = null;
    
    if (!empty($studentEmail)) {
        error_log('Attempting to send email to: ' . $studentEmail);
        
        try {
            // Use the private function to send email
            $this->sendDeletionEmail(
                $mailer,
                $studentEmail,
                $studentName,
                $projet->getTitreProjet(),
                $deleteReason,
                $deleteDetails
            );
            $emailSent = true;
            error_log('✅ EMAIL SENT SUCCESSFULLY');
            
        } catch (\Exception $e) {
            $emailErrorMessage = $e->getMessage();
            error_log('❌ EMAIL FAILED: ' . $emailErrorMessage);
            error_log('Exception type: ' . get_class($e));
            error_log('File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('Trace: ' . $e->getTraceAsString());
        }
    } else {
        error_log('❌ No student email provided - skipping email');
    }
    
    // Now delete the project
    error_log('Attempting to delete project...');
    try {
        $em->remove($projet);
        $em->flush();
        error_log('✅ Project deleted successfully');
    } catch (\Exception $e) {
        error_log('❌ Error deleting project: ' . $e->getMessage());
        $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        return $this->redirectToRoute('portfolio_list');
    }
    
    error_log('========== DELETE METHOD COMPLETED ==========');
    
    // Add flash messages based on email status
    if ($emailSent) {
        $this->addFlash('success', 'Projet supprimé et email envoyé avec succès !');
    } elseif (!empty($studentEmail)) {
        $this->addFlash('warning', 'Projet supprimé mais l\'email n\'a pas pu être envoyé. Erreur: ' . $emailErrorMessage);
    } else {
        $this->addFlash('success', 'Projet supprimé avec succès !');
        $this->addFlash('info', 'Aucun email étudiant trouvé pour notification.');
    }
    
    return $this->redirectToRoute('portfolio_list');
}*/

#[Route('/project/{id}/delete', name: 'project_delete', methods: ['POST'])]
public function delete(
    Request $request,
    EntityManagerInterface $em,
    ProjetRepository $projetRepository,
    \Symfony\Component\Mailer\MailerInterface $mailer,
    int $id
): Response
{
    // LOG EVERYTHING
    error_log('========== DELETE METHOD CALLED ==========');
    error_log('Time: ' . date('Y-m-d H:i:s'));
    error_log('Project ID: ' . $id);
    error_log('Request Method: ' . $request->getMethod());
    error_log('POST data: ' . json_encode($request->request->all()));
    
    // Find the project
    $projet = $projetRepository->find($id);
    if (!$projet) {
        error_log('ERROR: Project not found');
        $this->addFlash('error', 'Projet non trouvé');
        return $this->redirectToRoute('portfolio_list');
    }
    error_log('Project found: ' . $projet->getTitreProjet());
    
    // CSRF check
    $submittedToken = $request->request->get('_token');
    error_log('CSRF Token: ' . $submittedToken);
    
    if (!$this->isCsrfTokenValid('delete-project-' . $projet->getId(), $submittedToken)) {
        error_log('ERROR: Invalid CSRF token');
        $this->addFlash('error', 'Token CSRF invalide.');
        return $this->redirectToRoute('portfolio_list');
    }
    error_log('CSRF token valid');
    
    // Get form data
    $studentEmail = $request->request->get('student_email', '');
    $studentName = $request->request->get('student_name', '');
    $deleteReason = $request->request->get('delete_reason', '');
    $deleteDetails = $request->request->get('delete_details', '');
    
    error_log('Student Email: ' . $studentEmail);
    error_log('Student Name: ' . $studentName);
    error_log('Delete Reason: ' . $deleteReason);
    
    // Try to send email
    if (!empty($studentEmail)) {
        error_log('ATTEMPTING TO SEND EMAIL TO: ' . $studentEmail);
        
        try {
            // Create a simple test email first to isolate the issue
            $testEmail = (new \Symfony\Component\Mime\Email())
                ->from('nonoreply167@gmail.com')
                ->to($studentEmail)
                ->subject('TEST - Project Deletion')
                ->text('This is a test email from the delete method');
            
            error_log('Sending test email...');
            $mailer->send($testEmail);
            error_log('✅ TEST EMAIL SENT SUCCESSFULLY');
            
            // Now send the real email
            $email = (new \Symfony\Component\Mime\Email())
                ->from('nonoreply167@gmail.com')
                ->to($studentEmail)
                ->subject('Suppression de votre projet - Path2learn')
                ->html($this->renderView('emails/project_deleted.html.twig', [
                    'student_name' => $studentName,
                    'project_title' => $projet->getTitreProjet(),
                    'reason' => $deleteReason,
                    'details' => $deleteDetails,
                    'date' => new \DateTime()
                ]));
            
            error_log('Sending real email...');
            $mailer->send($email);
            error_log('✅ REAL EMAIL SENT SUCCESSFULLY');
            
            $this->addFlash('success', 'Projet supprimé et email envoyé avec succès !');
            
        } catch (\Exception $e) {
            error_log('❌ EMAIL FAILED: ' . $e->getMessage());
            error_log('File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('Trace: ' . $e->getTraceAsString());
            $this->addFlash('warning', 'Projet supprimé mais email non envoyé: ' . $e->getMessage());
        }
    } else {
        error_log('❌ No student email provided');
        $this->addFlash('success', 'Projet supprimé avec succès !');
        $this->addFlash('info', 'Aucun email étudiant trouvé.');
    }
    
    // Delete the project
    error_log('Deleting project...');
    $em->remove($projet);
    $em->flush();
    error_log('✅ Project deleted');
    error_log('========== DELETE METHOD ENDED ==========');
    
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
    error_log('--- sendDeletionEmail STARTED ---');
    error_log('Preparing email for: ' . $studentEmail);
    
    try {
        // Render the email template
        $htmlContent = $this->renderView('emails/project_deleted.html.twig', [
            'student_name' => $studentName,
            'project_title' => $projectTitle,
            'reason' => $reason,
            'details' => $details,
            'date' => new \DateTime()
        ]);
        
        error_log('Email template rendered, length: ' . strlen($htmlContent));
        
        $email = (new \Symfony\Component\Mime\Email())
            ->from('nonoreply167@gmail.com')
            ->to($studentEmail)
            ->subject('Suppression de votre projet - Portfolio')
            ->html($htmlContent);
        
        error_log('Email object created, attempting to send...');
        
        // Try to send
        $mailer->send($email);
        
        error_log('✅ EMAIL SENT SUCCESSFULLY to: ' . $studentEmail);
        error_log('--- sendDeletionEmail COMPLETED ---');
        
    } catch (\Exception $e) {
        error_log('❌ EMAIL FAILED in sendDeletionEmail: ' . $e->getMessage());
        error_log('File: ' . $e->getFile() . ':' . $e->getLine());
        error_log('Trace: ' . $e->getTraceAsString());
        throw $e;
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






#[Route('/test-email-send', name: 'test_email_send')]
public function testEmailSend(\Symfony\Component\Mailer\MailerInterface $mailer): Response
{
    try {
        // Try with a simpler email first
        $email = (new \Symfony\Component\Mime\Email())
            ->from('nonoreply167@gmail.com')
            ->to('hellourbanelegance@gmail.com')
            ->subject('Test Email - ' . date('Y-m-d H:i:s'))
            ->text('This is a plain text test email.')
            ->html('<h1>Test</h1><p>This is a test email with HTML.</p>');
        
        $mailer->send($email);
        
        // Also try sending to the same Gmail account (sometimes Gmail blocks sending to external domains)
        $email2 = (new \Symfony\Component\Mime\Email())
            ->from('nonoreply167@gmail.com')
            ->to('nonoreply167@gmail.com') // Send to yourself
            ->subject('Self Test - ' . date('Y-m-d H:i:s'))
            ->html('<h1>Self Test</h1><p>This is a test to your own Gmail.</p>');
        
        $mailer->send($email2);
        
        return new Response('✅ Test emails sent to hellourbanelegance@gmail.com and nonoreply167@gmail.com');
    } catch (\Exception $e) {
        return new Response('❌ Error: ' . $e->getMessage());
    }
}

}






