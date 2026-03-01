<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Enum\UserRole;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\MailerService;

class LiveController extends AbstractController
{
    #[Route('/live/teacher/{courseId}', name: 'app_live_teacher')]
    #[IsGranted('ROLE_USER')]
    public function teacherLive(int $courseId, CoursRepository $coursRepository): Response
    {
        $course = $coursRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Cours non trouvé');
        }

        // Vérifier que c'est bien l'enseignant du cours ou un Admin
        if ($course->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas les droits pour démarrer le live de ce cours.");
        }

        return $this->render('live/live_teacher.html.twig', [
            'course' => $course,
            'mercure_url' => $this->getParameter('mercure.default_hub')
        ]);
    }

    #[Route('/live/student/{courseId}', name: 'app_live_student')]
    #[IsGranted('ROLE_USER')]
    public function studentLive(int $courseId, CoursRepository $coursRepository): Response
    {
        $course = $coursRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Cours non trouvé');
        }

        return $this->render('live/live_student.html.twig', [
            'course' => $course,
            'mercure_url' => $this->getParameter('mercure.default_hub')
        ]);
    }

    #[Route('/live/start/{courseId}', name: 'app_live_start', methods: ['POST'])]
public function startLive(
    int $courseId,
    CoursRepository $coursRepository,
    UserRepository $userRepository,
    MailerService $mailerService,        // ← remplace MailerInterface + UrlGeneratorInterface
    UrlGeneratorInterface $urlGenerator
): JsonResponse {
    $course = $coursRepository->find($courseId);
    if (!$course || ($course->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN'))) {
        return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $students = $userRepository->findBy(['role' => UserRole::STUDENT]);
    $liveUrl = $urlGenerator->generate('app_live_student', ['courseId' => $courseId], UrlGeneratorInterface::ABSOLUTE_URL);

    $errors = [];
    foreach ($students as $student) {
        $sent = $mailerService->sendLiveStartNotification(
            $course,
            $student->getEmail(),
            $student->getPrenom(),
            $liveUrl
        );
        if (!$sent) {
            $errors[] = $student->getEmail();
        }
    }

    if (!empty($errors)) {
        return new JsonResponse(['status' => 'Partial success', 'errors' => $errors], 500);
    }

    return new JsonResponse(['status' => 'Live started and emails sent']);
}
    #[Route('/live/end/{courseId}', name: 'app_live_end', methods: ['POST'])]
    public function endLive(int $courseId, CoursRepository $coursRepository): JsonResponse
    {
        $course = $coursRepository->find($courseId);
        if (!$course || ($course->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN'))) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        return new JsonResponse(['status' => 'Live ended']);
    }

    #[Route('/live/message/{courseId}', name: 'app_live_message', methods: ['POST'])]
    public function sendMessage(): JsonResponse
    {
        return new JsonResponse(['status' => 'Feature disabled: Mercure not installed']);
    }

    #[Route('/live/reaction/{courseId}', name: 'app_live_reaction', methods: ['POST'])]
    public function sendReaction(): JsonResponse
    {
        return new JsonResponse(['status' => 'Feature disabled: Mercure not installed']);
    }
}
