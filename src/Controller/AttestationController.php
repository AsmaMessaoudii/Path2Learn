<?php
// src/Controller/AttestationController.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AttestationController extends AbstractController
{
    #[Route('/attestation/generate', name: 'attestation_generate')]
    #[IsGranted('ROLE_STUDENT')]
    public function generateAttestation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer toutes les questions
        $questions = $entityManager->getRepository(Question::class)->findAll();
        
        // Récupérer les réponses de l'utilisateur depuis la session
        $session = $request->getSession();
        $userResponses = $session->get('quiz_responses_' . $user->getId(), []);
        
        // Calculer les statistiques
        $totalQuestions = count($questions);
        $answeredQuestions = count($userResponses);
        $totalScore = 0;
        $maxPossibleScore = 0;
        $correctAnswers = 0;
        
        $details = [];
        
        foreach ($questions as $question) {
            $questionId = $question->getId();
            $questionKey = 'question_' . $questionId;
            $maxScore = $question->getNoteMax();
            $maxPossibleScore += $maxScore;
            
            if (isset($userResponses[$questionKey])) {
                $response = $userResponses[$questionKey];
                $score = $response['score'];
                $totalScore += $score;
                
                if ($score > 0) {
                    $correctAnswers++;
                }
                
                $details[] = [
                    'title' => $question->getTitre(),
                    'maxScore' => $maxScore,
                    'score' => $score,
                    'isCorrect' => $score > 0,
                    'status' => $score == $maxScore ? 'Parfait' : ($score > 0 ? 'Partiel' : 'Incorrect')
                ];
            } else {
                $details[] = [
                    'title' => $question->getTitre(),
                    'maxScore' => $maxScore,
                    'score' => 0,
                    'isCorrect' => false,
                    'status' => 'Non traitée'
                ];
            }
        }
        
        // Calculer le pourcentage
        $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100) : 0;
        
        // Déterminer la mention
        $mention = $this->getMention($percentage);
        
        // Déterminer si certifié
        $isCertified = $answeredQuestions == $totalQuestions && $percentage >= 50;
        
        // Date d'obtention
        $completionDate = new \DateTime();
        
        // Générer le PDF
        return $this->renderPdf('attestation/pdf_template.html.twig', [
            'user' => $user,
            'totalQuestions' => $totalQuestions,
            'answeredQuestions' => $answeredQuestions,
            'totalScore' => $totalScore,
            'maxPossibleScore' => $maxPossibleScore,
            'percentage' => $percentage,
            'mention' => $mention,
            'isCertified' => $isCertified,
            'correctAnswers' => $correctAnswers,
            'details' => $details,
            'completionDate' => $completionDate,
            'certificateNumber' => $this->generateCertificateNumber($user, $completionDate)
        ]);
    }

    #[Route('/attestation/view', name: 'attestation_view')]
    #[IsGranted('ROLE_STUDENT')]
    public function viewAttestation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer toutes les questions
        $questions = $entityManager->getRepository(Question::class)->findAll();
        
        // Récupérer les réponses de l'utilisateur depuis la session
        $session = $request->getSession();
        $userResponses = $session->get('quiz_responses_' . $user->getId(), []);
        
        // Calculer les statistiques
        $totalQuestions = count($questions);
        $answeredQuestions = count($userResponses);
        $totalScore = 0;
        $maxPossibleScore = 0;
        
        foreach ($questions as $question) {
            $questionId = $question->getId();
            $questionKey = 'question_' . $questionId;
            $maxPossibleScore += $question->getNoteMax();
            
            if (isset($userResponses[$questionKey])) {
                $totalScore += $userResponses[$questionKey]['score'];
            }
        }
        
        $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100) : 0;
        $mention = $this->getMention($percentage);
        $isCertified = $answeredQuestions == $totalQuestions && $percentage >= 50;
        
        return $this->render('attestation/view.html.twig', [
            'user' => $user,
            'totalQuestions' => $totalQuestions,
            'answeredQuestions' => $answeredQuestions,
            'totalScore' => $totalScore,
            'maxPossibleScore' => $maxPossibleScore,
            'percentage' => $percentage,
            'mention' => $mention,
            'isCertified' => $isCertified
        ]);
    }

    #[Route('/attestation/teacher/view/{userId}', name: 'attestation_teacher_view')]
    #[IsGranted('ROLE_TEACHER')]
    public function teacherViewAttestation(int $userId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($userId);
        
        if (!$user) {
            throw $this->createNotFoundException('Étudiant non trouvé');
        }
        
        // Récupérer toutes les questions
        $questions = $entityManager->getRepository(Question::class)->findAll();
        
        // Récupérer les réponses de l'utilisateur depuis la session
        $session = $request->getSession();
        $userResponses = $session->get('quiz_responses_' . $user->getId(), []);
        
        // Calculer les statistiques
        $totalQuestions = count($questions);
        $answeredQuestions = count($userResponses);
        $totalScore = 0;
        $maxPossibleScore = 0;
        
        foreach ($questions as $question) {
            $questionId = $question->getId();
            $questionKey = 'question_' . $questionId;
            $maxPossibleScore += $question->getNoteMax();
            
            if (isset($userResponses[$questionKey])) {
                $totalScore += $userResponses[$questionKey]['score'];
            }
        }
        
        $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100) : 0;
        $mention = $this->getMention($percentage);
        $isCertified = $answeredQuestions == $totalQuestions && $percentage >= 50;
        
        return $this->render('attestation/teacher_view.html.twig', [
            'student' => $user,
            'totalQuestions' => $totalQuestions,
            'answeredQuestions' => $answeredQuestions,
            'totalScore' => $totalScore,
            'maxPossibleScore' => $maxPossibleScore,
            'percentage' => $percentage,
            'mention' => $mention,
            'isCertified' => $isCertified
        ]);
    }

    private function renderPdf(string $template, array $data): Response
    {
        // Configuration de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        // Créer une instance de Dompdf
        $dompdf = new Dompdf($pdfOptions);
        
        // Générer le HTML à partir du template
        $html = $this->renderView($template, $data);
        
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        
        // Définir le format du papier (A4 paysage pour une attestation)
        $dompdf->setPaper('A4', 'landscape');
        
        // Rendre le PDF
        $dompdf->render();
        
        // Générer le nom du fichier
        $filename = sprintf('attestation_%s_%s.pdf', 
            $data['user']->getNom(), 
            $data['user']->getPrenom()
        );
        
        // Retourner le PDF en téléchargement
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    private function getMention(int $percentage): string
    {
        if ($percentage >= 90) {
            return 'Très Bien';
        } elseif ($percentage >= 75) {
            return 'Bien';
        } elseif ($percentage >= 60) {
            return 'Assez Bien';
        } elseif ($percentage >= 50) {
            return 'Passable';
        } else {
            return 'Non Certifié';
        }
    }

    private function generateCertificateNumber(User $user, \DateTime $date): string
    {
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        $userId = str_pad($user->getId(), 4, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(uniqid(), -4));
        
        return sprintf('CERT-%s%s%s-%s-%s', $year, $month, $day, $userId, $random);
    }
}