<?php
// src/Controller/TestOpenAIController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TestOpenAIController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? 'sk-...'; // mettre ta clé ici ou dans .env
    }

    #[Route('/test-openai', name: 'test_openai')]
    public function testOpenAI(): JsonResponse
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                'https://api.openai.com/v1/chat/completions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type'  => 'application/json'
                    ],
                    'json' => [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'user', 'content' => 'Dis bonjour en français en une phrase']
                        ],
                        'temperature' => 0.6,
                        'max_tokens' => 50
                    ]
                ]
            );

            $data = $response->toArray();
            return $this->json([
                'success' => true,
                'response' => $data
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode()
            ]);
        }
    }
}
