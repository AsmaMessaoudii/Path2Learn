<?php
// src/Controller/TestGeminiController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TestGeminiController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/test-gemini', name: 'test_gemini')]
    public function testGemini(): JsonResponse
    {
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? null;
    
        if (!$apiKey) {
            return $this->json(['success' => false, 'error' => 'Clé API non trouvée']);
        }
    
        try {
            $response = $this->httpClient->request(
                'POST',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey,
                [
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => 'Dis bonjour en français en une phrase']
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.6,
                            'maxOutputTokens' => 200
                        ]
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ]
                ]
            );
    
            $data = $response->toArray();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Pas de réponse';
    
            return $this->json([
                'success' => true,
                'text' => $text,
                'api_key_preview' => substr($apiKey, 0, 10) . '...'
            ]);
    
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'api_key_preview' => substr($apiKey, 0, 10) . '...'
            ]);
        }
    }

}