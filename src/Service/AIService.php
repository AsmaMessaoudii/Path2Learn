<?php
// src/Service/AIService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class AIService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $apiKey;
    private string $apiUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        string $apiKey,
        string $apiUrl
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
    }

    public function generateChoices(string $question, int $nbChoices = 4): array
    {
        try {
            $this->logger->info('Tentative de génération Groq', [
                'question' => substr($question, 0, 100),
                'nbChoices' => $nbChoices
            ]);
            
            $prompt = $this->buildChoicesPrompt($question, $nbChoices);
            
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    // Modèles disponibles sur Groq
                   'model' => 'llama-3.3-70b-versatile', // Ou 'llama3-70b-8192', 'llama3-8b-8192'
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un assistant pédagogique expert en création de questions à choix multiples. Tu génères des réponses pertinentes et variées. Réponds TOUJOURS en français et au format JSON demandé.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1024
                ]
            ]);

            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                $content = $response->getContent(false);
                $this->logger->error('Erreur API Groq', [
                    'status' => $statusCode,
                    'response' => substr($content, 0, 500)
                ]);
                return $this->getFallbackChoices($question);
            }

            $data = $response->toArray();
            $assistantContent = $data['choices'][0]['message']['content'] ?? '';
            
            return $this->parseChoicesResponse($assistantContent, $question);
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'appel Groq', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->getFallbackChoices($question);
        }
    }

    public function generateExplanation(string $question, string $correctAnswer, array $allChoices): string
    {
        try {
            $prompt = $this->buildExplanationPrompt($question, $correctAnswer, $allChoices);
            
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es un professeur expert qui explique clairement pourquoi une réponse est correcte. Réponds en français, de manière pédagogique et concise.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.5,
                    'max_tokens' => 512
                ]
            ]);

            $data = $response->toArray();
            return $data['choices'][0]['message']['content'] ?? 
                   "Explication non disponible. La bonne réponse est : " . $correctAnswer;
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur explication Groq', [
                'message' => $e->getMessage()
            ]);
            return "Cette réponse est correcte car elle correspond aux critères de la question.";
        }
    }

  private function buildChoicesPrompt(string $question, int $nbChoices): string
{
    $nbWrong = $nbChoices - 1;
    
    return <<<EOT
    Pour la question suivante: "$question"
    
    Génère $nbChoices propositions de réponses à choix multiples:
    - Une bonne réponse (correcte)
    - $nbWrong mauvaises réponses (pertinentes mais incorrectes)
    
    Réponds UNIQUEMENT avec un objet JSON valide au format suivant, sans texte avant ni après :
    {
        "choices": [
            {"text": "réponse 1", "isCorrect": true},
            {"text": "réponse 2", "isCorrect": false},
            {"text": "réponse 3", "isCorrect": false},
            {"text": "réponse 4", "isCorrect": false}
        ],
        "explanation": "Brève explication pédagogique de la bonne réponse (2-3 phrases)"
    }
    
    Important: 
    - Toutes les réponses doivent être en français
    - Les réponses doivent être pertinentes par rapport à la question
    - Une seule réponse doit être correcte
    EOT;
}

    private function buildExplanationPrompt(string $question, string $correctAnswer, array $allChoices): string
    {
        $choicesList = implode("\n", array_map(function($c) {
            return "- " . $c['contenu'] . ($c['estCorrect'] ? ' (bonne réponse)' : '');
        }, $allChoices));

        return <<<EOT
        Question: "$question"
        
        Réponses proposées:
        $choicesList
        
        La bonne réponse est: "$correctAnswer"
        
        Explique de manière pédagogique pourquoi cette réponse est correcte et pourquoi les autres sont incorrectes.
        Donne une explication claire et concise (max 150 mots) en français.
        EOT;
    }

    private function parseChoicesResponse(string $content, string $originalQuestion): array
    {
        // Essayer d'extraire le JSON
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            try {
                $data = json_decode($matches[0], true);
                if (isset($data['choices']) && is_array($data['choices']) && count($data['choices']) > 0) {
                    // Nettoyer et valider les choix
                    $validChoices = [];
                    $hasCorrect = false;
                    
                    foreach ($data['choices'] as $choice) {
                        if (isset($choice['text']) && isset($choice['isCorrect'])) {
                            if ($choice['isCorrect']) {
                                if (!$hasCorrect) {
                                    $hasCorrect = true;
                                    $validChoices[] = $choice;
                                } else {
                                    $validChoices[] = [
                                        'text' => $choice['text'],
                                        'isCorrect' => false
                                    ];
                                }
                            } else {
                                $validChoices[] = $choice;
                            }
                        }
                    }
                    
                    if (!$hasCorrect && !empty($validChoices)) {
                        $validChoices[0]['isCorrect'] = true;
                    }
                    
                    return [
                        'choices' => $validChoices,
                        'explanation' => $data['explanation'] ?? 'Choix générés avec Groq AI'
                    ];
                }
            } catch (\Exception $e) {
                $this->logger->warning('Erreur parsing JSON Groq', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $this->getFallbackChoices($originalQuestion);
    }

    private function getFallbackChoices(string $question): array
    {
        $questionLower = strtolower($question);
        
        if (strpos($questionLower, 'capital') !== false || strpos($questionLower, 'capitale') !== false) {
            return [
                'choices' => [
                    ['text' => 'Paris', 'isCorrect' => true],
                    ['text' => 'Lyon', 'isCorrect' => false],
                    ['text' => 'Marseille', 'isCorrect' => false],
                    ['text' => 'Bordeaux', 'isCorrect' => false],
                ],
                'explanation' => 'Paris est la capitale de la France.'
            ];
        }
        
        return [
            'choices' => [
                ['text' => 'Option A', 'isCorrect' => true],
                ['text' => 'Option B', 'isCorrect' => false],
                ['text' => 'Option C', 'isCorrect' => false],
                ['text' => 'Option D', 'isCorrect' => false],
            ],
            'explanation' => 'Cette réponse a été générée par Groq AI.'
        ];
    }
    // À ajouter dans AIService.php
public function getApiKey(): string
{
    return $this->apiKey;
}

public function getApiUrl(): string
{
    return $this->apiUrl;
}
public function analyzeDifficulty(string $question, array $choices): array
{
    try {
        $choicesList = implode("\n", array_map(function($c) {
            return "- " . $c['contenu'] . ($c['estCorrect'] ? ' (bonne réponse)' : '');
        }, $choices));

        $prompt = <<<EOT
        Analyse cette question de quiz et détermine son niveau de difficulté.
        
        Question: "$question"
        
        Choix proposés:
        $choicesList
        
        Réponds UNIQUEMENT avec un objet JSON valide, sans texte avant ni après:
        {
            "niveau": "Facile" | "Moyen" | "Difficile" | "Expert",
            "score_difficulte": <nombre entre 1 et 10>,
            "niveau_etudiant": "Débutant" | "Intermédiaire" | "Avancé" | "Expert",
            "justification": "Explication courte (2-3 phrases) du niveau de difficulté",
            "competences_requises": ["compétence 1", "compétence 2"],
            "conseils_etudiant": "Conseil personnalisé pour l'étudiant selon son niveau"
        }
        EOT;

        $response = $this->httpClient->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu es un expert pédagogique qui évalue le niveau de difficulté des questions de quiz. Tu réponds toujours en français et au format JSON demandé.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 512
            ]
        ]);

        $data = $response->toArray();
        $content = $data['choices'][0]['message']['content'] ?? '';

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $result = json_decode($matches[0], true);
            if ($result && isset($result['niveau'])) {
                return $result;
            }
        }

        return $this->getFallbackDifficulty();

    } catch (\Exception $e) {
        $this->logger->error('Erreur analyse difficulté Groq', [
            'message' => $e->getMessage()
        ]);
        return $this->getFallbackDifficulty();
    }
}

private function getFallbackDifficulty(): array
{
    return [
        'niveau' => 'Moyen',
        'score_difficulte' => 5,
        'niveau_etudiant' => 'Intermédiaire',
        'justification' => 'Analyse non disponible pour le moment.',
        'competences_requises' => ['Connaissances générales'],
        'conseils_etudiant' => 'Révisez le cours correspondant avant de répondre.'
    ];
}
}