<?php

namespace App\Controller;

use App\Service\OpenAIService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AiChatController extends AbstractController
{
    #[Route('/ai/chat', name: 'ai_chat', methods: ['POST'])]
    public function chat(Request $request, OpenAIService $ai): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');

        if (!$message) {
            return new JsonResponse(['reply' => "Message vide 😅"]);
        }

        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        // Get conversation history from session
        $history = $session->get('ai_chat_history', []);

        // Ask Gemini (with fallback)
        $reply = $ai->ask($message, $history);

        // Add both user message and assistant reply to history
        $history[] = ['role' => 'user', 'content' => $message];
        $history[] = ['role' => 'assistant', 'content' => $reply];

        $session->set('ai_chat_history', $history);

        return new JsonResponse(['reply' => $reply]);
    }

    #[Route('/ai/chat/reset', name: 'ai_chat_reset', methods: ['POST'])]
    public function resetChat(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session->remove('ai_chat_history');

        return new JsonResponse(['reply' => '🧹 Historique de conversation réinitialisé.']);
    }
}