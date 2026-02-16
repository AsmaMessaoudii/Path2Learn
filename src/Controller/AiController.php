<?php
// src/Controller/AiController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/ai/generate-description', name: 'ai_generate_description', methods: ['POST'])]
public function generateDescription(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $title = $data['title'] ?? '';
    $keywords = $data['keywords'] ?? '';
    $technologies = $data['technologies'] ?? '';

    if (empty($title)) {
        return $this->json(['error' => 'Veuillez fournir un titre'], 400);
    }

    $apiKey = $_ENV['GEMINI_API_KEY'];

    // Prompt amélioré
    $prompt = "Génère une description professionnelle COMPLÈTE et DÉTAILLÉE pour le projet suivant.\n\n";
    $prompt .= "Titre du projet: " . $title . "\n";
    if (!empty($keywords)) {
        $prompt .= "Mots-clés: " . $keywords . "\n";
    }
    if (!empty($technologies)) {
        $prompt .= "Technologies utilisées: " . $technologies . "\n";
    }
    $prompt .= "\nIMPORTANT - La description doit OBLIGATOIREMENT contenir ces 5 sections :\n\n";
    $prompt .= "1. **Objectif du projet** (2-3 phrases) : Expliquer le but et la problématique résolue.\n";
    $prompt .= "2. **Fonctionnalités principales** (3-4 phrases) : Décrire ce que l'utilisateur peut faire.\n";
    $prompt .= "3. **Architecture technique** (3-4 phrases) : Parler des technologies et de l'architecture.\n";
    $prompt .= "4. **Défis techniques** (2-3 phrases) : Mentionner les difficultés rencontrées et solutions.\n";
    $prompt .= "5. **Résultats et impact** (2-3 phrases) : Parler des bénéfices et résultats obtenus.\n\n";
    $prompt .= "Consignes :\n";
    $prompt .= "- Écris en français professionnel\n";
    $prompt .= "- Utilise des phrases complètes et naturelles\n";
    $prompt .= "- Sois précis et concret\n";
    $prompt .= "- La description doit faire environ 300 à 400 mots (maximum 2000 caractères)\n"; // AJOUTÉ
    $prompt .= "- Commence DIRECTEMENT par le premier paragraphe, sans introduction";

    try {
        $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey, [
            'json' => [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.8,
                    'maxOutputTokens' => 2048,
                    'topP' => 0.95,
                ]
            ]
        ]);

        $responseData = $response->toArray();
        
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            $description = $responseData['candidates'][0]['content']['parts'][0]['text'];
            $description = trim($description);
            
            // 🔥 LIMITATION À 2000 CARACTÈRES MAXIMUM
            if (strlen($description) > 2000) {
                // Option 1: Couper proprement à la dernière phrase
                $description = substr($description, 0, 2000);
                // Trouver le dernier point pour couper proprement
                $lastPeriod = strrpos($description, '.');
                if ($lastPeriod > 1500) { // Si on a un point après 1500 caractères
                    $description = substr($description, 0, $lastPeriod + 1);
                } else {
                    // Sinon, couper à 1997 et ajouter ...
                    $description = substr($description, 0, 1997) . '...';
                }
            }
            
            return $this->json(['description' => $description]);
        } else {
            return $this->json(['error' => 'Réponse invalide de l\'API'], 500);
        }
    } catch (\Exception $e) {
        return $this->json(['error' => 'Erreur: ' . $e->getMessage()], 500);
    }
}

    #[Route('/ai/test', name: 'ai_test')]
    public function test(): Response
    {
        return $this->render('ai/test.html.twig');
    }
}