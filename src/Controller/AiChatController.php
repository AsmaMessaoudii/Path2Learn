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
        $message = $data['message'] ?? '';

        if (!$message) {
            return new JsonResponse(['reply' => "Message vide 😅"]);
        }

        $reply = $ai->ask($message);

        return new JsonResponse([
            'reply' => $reply
        ]);
    }
}
