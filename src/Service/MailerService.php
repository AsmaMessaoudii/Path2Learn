<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class MailerService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private ?LoggerInterface $logger;
    private string $senderEmail;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        ?LoggerInterface $logger = null,
        string $senderEmail = 'pathlearnnotifications@gmail.com'
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->senderEmail = $senderEmail;
    }

    public function sendBadRatingNotification($course, $evaluation, $badRatingReason): bool
    {
        $profEmail = $course->getEmailProf();
        
        $this->log('info', 'Tentative d\'envoi email à: ' . $profEmail);
        
        if (!$profEmail || !filter_var($profEmail, FILTER_VALIDATE_EMAIL)) {
            $this->log('error', 'Email du professeur invalide: ' . $profEmail);
            return false;
        }

        try {
            $subject = sprintf('[Path2Learn] Alerte: Mauvaise évaluation pour "%s"', $course->getTitre());
            
            $htmlContent = $this->twig->render('emails/bad_rating_notification.html.twig', [
                'course' => $course,
                'evaluation' => $evaluation,
                'reason' => $badRatingReason,
            ]);

            $email = (new Email())
                ->from($this->senderEmail)
                ->to($profEmail)
                ->subject($subject)
                ->html($htmlContent);

            $this->mailer->send($email);
            
            $this->log('info', 'Email envoyé avec succès à ' . $profEmail);
            return true;
            
        } catch (\Exception $e) {
            $this->log('error', 'Erreur envoi email: ' . $e->getMessage());
            return false;
        }
    }

    private function log(string $level, string $message): void
    {
        if ($this->logger) {
            $this->logger->$level($message);
        } else {
            error_log("[$level] $message");
        }
    }
    public function sendLiveStartNotification($course, string $studentEmail, string $studentPrenom, string $liveUrl): bool
{
    try {
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($studentEmail)
            ->subject('🔴 Live commencé : ' . $course->getTitre())
            ->html(sprintf(
                '<h1>Bonjour %s,</h1>
                <p>Le live pour le cours <strong>%s</strong> vient de commencer.</p>
                <p><a href="%s" style="padding:10px 20px;background:#ef4444;color:white;text-decoration:none;border-radius:5px;">Rejoindre le Live</a></p>
                <p>L\'équipe Path2Learn</p>',
                $studentPrenom,
                $course->getTitre(),
                $liveUrl
            ));

        $this->mailer->send($email);
        return true;
    } catch (\Exception $e) {
        $this->log('error', 'Erreur envoi live email à ' . $studentEmail . ': ' . $e->getMessage());
        return false;
    }
}
}