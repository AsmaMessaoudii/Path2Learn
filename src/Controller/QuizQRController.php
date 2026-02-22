<?php
// src/Controller/QuizQRController.php

namespace App\Controller;

use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizQRController extends AbstractController
{
    // ============ SEULEMENT QR CODE AVEC TEXTE ============
    
    #[Route('/quiz/qr/text/{id}', name: 'quiz_qr_text')]
    public function generateQRWithText(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $question = $entityManager->getRepository(Question::class)->find($id);
        
        if (!$question) {
            throw $this->createNotFoundException('Question non trouvée');
        }

        // Construire le texte compact à mettre dans le QR code
        $qrText = $this->buildCompactQuestionText($question);

        // Créer le QR code avec le texte
        $qrCode = new QrCode($qrText);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'inline; filename="question-' . $id . '-text.png"'
            ]
        );
    }

    #[Route('/quiz/qr/download-text/{id}', name: 'quiz_qr_download_text')]
    public function downloadQRWithText(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $question = $entityManager->getRepository(Question::class)->find($id);
        
        if (!$question) {
            throw $this->createNotFoundException('Question non trouvée');
        }

        // Construire le texte compact
        $qrText = $this->buildCompactQuestionText($question);

        // Créer le QR code
        $qrCode = new QrCode($qrText);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="question-' . $id . '-text.png"'
            ]
        );
    }

    #[Route('/quiz/qr/view-text/{id}', name: 'quiz_qr_view_text')]
    public function viewQRWithText(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $question = $entityManager->getRepository(Question::class)->find($id);
        
        if (!$question) {
            throw $this->createNotFoundException('Question non trouvée');
        }

        return $this->render('quiz/qr_view_text.html.twig', [
            'questionId' => $id,
            'question' => $question,
            'currentUser' => $user,
            'qrText' => $this->buildCompactQuestionText($question)
        ]);
    }

    // ============ MÉTHODES PRIVÉES ============
    
    /**
     * Construit un texte compact pour le QR code
     */
  /**
 * Construit un texte compact pour le QR code
 */
private function buildCompactQuestionText(Question $question): string
{
    $user = $this->getUser();
    $isTeacher = in_array('ROLE_TEACHER', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles());
    
    $lines = [];
    
    // Titre de la question
    $lines[] = "QUESTION: " . $this->shortenText($question->getTitre(), 60);
    
    // Description (si existe)
    $desc = strip_tags($question->getDescription());
    if (!empty($desc)) {
        $lines[] = "Description: " . $this->shortenText($desc, 70);
    }
    
    // Note et durée
    $info = "Note: " . $question->getNoteMax() . " points";
    if ($question->getDuree()) {
        $info .= " | Durée: " . $question->getDuree() . " min";
    }
    $lines[] = $info;
    
    // Ligne séparatrice
    $lines[] = "-------------------";
    
    // Choix - avec ou sans ✓ selon le rôle
    $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    foreach ($question->getChoix() as $index => $choice) {
        $prefix = $alphabet[$index] ?? ($index + 1);
        $contenu = $this->shortenText($choice->getContenu(), 45);
        
        // Ajouter ✓ seulement pour les enseignants/admins
        if ($isTeacher && $choice->isEstCorrect()) {
            $lines[] = $prefix . ". " . $contenu . " ✓";
        } else {
            $lines[] = $prefix . ". " . $contenu;
        }
    }
    
    // Ajouter la liste des réponses correctes seulement pour les enseignants/admins
    if ($isTeacher) {
        $correctAnswers = [];
        foreach ($question->getChoix() as $index => $choice) {
            if ($choice->isEstCorrect()) {
                $correctAnswers[] = $alphabet[$index] ?? ($index + 1);
            }
        }
        if (!empty($correctAnswers)) {
            $lines[] = "-------------------";
            $lines[] = "✓ Réponses: " . implode(', ', $correctAnswers);
        }
    }
    
    return implode("\n", $lines);
}

    /**
     * Raccourcit un texte si trop long
     */
    private function shortenText(string $text, int $maxLength): string
    {
        $text = trim($text);
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        return substr($text, 0, $maxLength - 3) . '...';
    }
}