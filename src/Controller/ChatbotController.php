<?php
// src/Controller/ChatbotController.php

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Repository\ChatMessageRepository;
use App\Repository\CoursRepository;
use App\Service\ChatbotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/chatbot')]
class ChatbotController extends AbstractController
{
    // ✅ Route de diagnostic — à supprimer après résolution
    #[Route('/test-direct', name: 'chatbot_test_direct', methods: ['GET'])]
    public function testDirect(HttpClientInterface $httpClient): JsonResponse
    {
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? 'NON TROUVÉE';

        try {
            $response = $httpClient->request(
                'POST',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey,
                [
                    'json' => [
                        'contents' => [['parts' => [['text' => 'Dis juste: OK']]]]
                    ],
                    'timeout' => 30
                ]
            );

            $statusCode = $response->getStatusCode();
            $raw = $response->toArray(false);

            return $this->json([
                'status_code'  => $statusCode,
                'api_key'      => substr($apiKey, 0, 10) . '...',
                'raw_response' => $raw
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'exception_message' => $e->getMessage(),
                'exception_code'    => $e->getCode(),
                'api_key'           => substr($apiKey, 0, 10) . '...',
            ]);
        }
    }

    #[Route('/ask/{id}', name: 'chatbot_ask', methods: ['POST'])]
    public function ask(
        int $id,
        Request $request,
        CoursRepository $coursRepository,
        ChatbotService $chatbotService,
        ChatMessageRepository $chatMessageRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cours = $coursRepository->find($id);
        if (!$cours || $cours->getStatut() !== 'publié') {
            return $this->json(['error' => 'Cours non trouvé'], 404);
        }

        $data     = json_decode($request->getContent(), true);
        $question = trim($data['question'] ?? '');

        if (empty($question)) {
            return $this->json(['error' => 'Question vide'], 400);
        }

        if (strlen($question) > 500) {
            return $this->json(['error' => 'Question trop longue (max 500 caractères)'], 400);
        }

        $user        = $this->getUser();
        $lastMessage = $chatMessageRepository->findLastByUser($cours, $user);

        if ($lastMessage) {
            $secondsElapsed = (new \DateTime())->getTimestamp() - $lastMessage->getCreatedAt()->getTimestamp();
            if ($secondsElapsed < 5) {
                return $this->json([
                    'error' => 'Attendez quelques secondes entre chaque question.'
                ], 429);
            }
        }

        try {
            $answer = $chatbotService->answer($question, $cours, $user);

            $message = new ChatMessage();
            $message->setCours($cours);
            $message->setUser($user);
            $message->setQuestion($question);
            $message->setAnswer($answer);

            $em->persist($message);
            $em->flush();

            return $this->json([
                'success'  => true,
                'answer'   => $answer,
                'question' => $question,
                'time'     => (new \DateTime())->format('H:i')
            ]);

        } catch (\Exception $e) {
            // ✅ Retourner l'erreur complète pour diagnostic
            return $this->json([
                'error'      => $e->getMessage(),
                'error_code' => $e->getCode(),
                'is_429'     => str_contains($e->getMessage(), '429')
            ], 500);
        }
    }

    #[Route('/history/{id}', name: 'chatbot_history', methods: ['GET'])]
    public function history(
        int $id,
        CoursRepository $coursRepository,
        ChatMessageRepository $chatMessageRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cours = $coursRepository->find($id);
        if (!$cours) {
            return $this->json(['error' => 'Cours non trouvé'], 404);
        }

        $messages = $chatMessageRepository->findByCoursAndUser($cours, $this->getUser(), 30);

        $data = array_map(fn($m) => [
            'question'   => $m->getQuestion(),
            'answer'     => $m->getAnswer(),
            'created_at' => $m->getCreatedAt()->format('H:i'),
        ], $messages);

        return $this->json(['success' => true, 'messages' => $data]);
    }

    #[Route('/clear/{id}', name: 'chatbot_clear', methods: ['POST'])]
    public function clear(
        int $id,
        CoursRepository $coursRepository,
        ChatMessageRepository $chatMessageRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cours = $coursRepository->find($id);
        if (!$cours) {
            return $this->json(['error' => 'Cours non trouvé'], 404);
        }

        $messages = $chatMessageRepository->findByCoursAndUser($cours, $this->getUser());
        foreach ($messages as $msg) {
            $em->remove($msg);
        }
        $em->flush();

        return $this->json(['success' => true]);
    }
}