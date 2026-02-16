<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class OpenAIService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private ?LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client,
        string $apiKey,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    public function ask(string $message): string
    {
        // Essayer l'API Gemini d'abord
        try {
            return $this->askGemini($message);
        } catch (\Exception $e) {
            // Fallback sur réponses intelligentes si API échoue
            if ($this->logger) {
                $this->logger->warning('Gemini API failed, using fallback', [
                    'error' => $e->getMessage()
                ]);
            }
            return $this->getSmartResponse($message);
        }
    }

    private function askGemini(string $message): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;

        $systemPrompt = "Tu es un assistant virtuel intelligent pour une plateforme éducative appelée 'Path2Learn'. 

Ton rôle:
- Aider les utilisateurs avec la connexion, inscription, mot de passe
- Répondre de manière professionnelle mais sympathique
- Donner des solutions concrètes et étape par étape
- Utiliser des emojis pour rendre la conversation agréable
- Répondre en français (sauf si l'utilisateur écrit en anglais)
- Être bref (maximum 4-5 lignes)

Informations importantes:
- Email support: support@path2learn.com
- Mot de passe: minimum 5 caractères, 1 majuscule, 1 minuscule
- Fonction 'Mot de passe oublié' disponible avec code à 6 chiffres par email
- Les comptes peuvent être bloqués s'ils sont désactivés par l'admin

Question de l'utilisateur: {$message}";

        $response = $this->client->request("POST", $url, [
            "headers" => [
                "Content-Type" => "application/json"
            ],
            "json" => [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $systemPrompt]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.8,
                    "topK" => 40,
                    "topP" => 0.95,
                    "maxOutputTokens" => 300,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    [
                        "category" => "HARM_CATEGORY_HARASSMENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                    ],
                    [
                        "category" => "HARM_CATEGORY_HATE_SPEECH",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                    ],
                    [
                        "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                    ],
                    [
                        "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                    ]
                ]
            ],
            "timeout" => 15
        ]);

        $data = $response->toArray();

        if (isset($data["candidates"][0]["content"]["parts"][0]["text"])) {
            return $data["candidates"][0]["content"]["parts"][0]["text"];
        }

        throw new \Exception("No valid response from Gemini");
    }

    private function getSmartResponse(string $message): string
    {
        $message = strtolower(trim($message));

        $keywords = [
            'bloque' => "🔒 Votre compte semble bloqué. Voici ce que vous pouvez faire:\n\n1️⃣ Utilisez 'Mot de passe oublié' pour réinitialiser\n2️⃣ Contactez l'admin: support@path2learn.com\n3️⃣ Vérifiez que votre statut est 'activé'\n\nBesoin d'aide supplémentaire?",
            
            'compte' => "Pour les problèmes de compte, essayez 'Mot de passe oublié' ou contactez support@path2learn.com 📧",
            
            'mot de passe' => "🔐 Réinitialisation:\n\n1️⃣ Cliquez 'Mot de passe oublié?'\n2️⃣ Entrez votre email\n3️⃣ Code à 6 chiffres par email (valide 15 min)\n4️⃣ Nouveau mot de passe: min 5 caractères, 1 majuscule, 1 minuscule",
            
            'connexion' => "🔑 Pour vous connecter, assurez-vous que:\n• Votre email est correct\n• Votre mot de passe est bon\n• Votre compte est activé\n\nProblème? Utilisez 'Mot de passe oublié'",
            
            'inscription' => "✍️ Pour créer un compte:\n\n1️⃣ Cliquez 'S'inscrire'\n2️⃣ Formulaire complet\n3️⃣ Rôle: Enseignant ou Étudiant\n4️⃣ Activation automatique!\n\nMot de passe: min 5 caractères",
            
            'email' => "📧 Email non reçu? Vérifiez:\n• Les SPAMS/Courrier indésirable\n• L'adresse email saisie\n• Attendez 2-3 minutes\n\nToujours rien? support@path2learn.com",
            
            'aide' => "💡 Je peux vous aider avec:\n\n🔹 Connexion et mot de passe\n🔹 Inscription\n🔹 Compte bloqué\n🔹 Problèmes techniques\n\nQue voulez-vous savoir?",
        ];

        foreach ($keywords as $keyword => $response) {
            if (str_contains($message, $keyword)) {
                return $response;
            }
        }

        return "👋 Bonjour! Je suis l'assistant Path2Learn.\n\nJe peux vous aider avec la connexion, l'inscription, les mots de passe, et plus encore.\n\n💬 Quelle est votre question?";
    }
}