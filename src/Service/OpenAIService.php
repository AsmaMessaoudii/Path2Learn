<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class OpenAIService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $model;
    private ?LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client,
        string $apiKey,
        string $model,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->model  = $model;
        $this->logger = $logger;
    }

    /**
     * Ask a message with full conversation history
     */
    public function ask(string $message, array $history = []): string
    {
        // Ajoute le message actuel à l'historique
        $history[] = ['role' => 'user', 'content' => $message];

        try {
            $reply = $this->askWithHistory($history);
            return $reply;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Groq API failed', [
                    'message' => $message,
                    'exception' => $e->getMessage()
                ]);
            }
            return $this->getSmartFallback($message);
        }
    }

    /**
     * Appel à Groq avec l'historique complet (format OpenAI)
     */
    /**
 * Appel à Groq avec l'historique complet (format OpenAI)
 */
public function askWithHistory(array $history): string
{
    $url = "https://api.groq.com/openai/v1/chat/completions";

    // Système prompt ultra-spécifique pour Path2Learn
    $systemPrompt = <<<PROMPT
Tu es l'assistant officiel de Path2Learn, une plateforme éducative universitaire tunisienne.
Ton rôle : aider les utilisateurs (étudiants, enseignants, admins) avec leurs problèmes sur le site.

Règles strictes :
- Réponds TOUJOURS en français (sauf si l'utilisateur écrit explicitement en anglais).
- Sois concis, direct, empathique et rassurant. Pas de blabla inutile.
- Concentre-toi uniquement sur les problèmes liés à Path2Learn : connexion, Face ID, mot de passe oublié, création de cours, quiz, événements, ressources, profil, paiements, blocage compte, dashboard étudiant/enseignant/admin, etc.
- Si la question n'est pas liée au site → réponds gentiment : "Désolé, je suis spécialisé dans l'aide sur Path2Learn. Pouvez-vous me parler d'un problème avec le site ? 😊"
- Donne des solutions claires + étapes précises quand possible.
- Si c'est urgent ou complexe (paiement bloqué, compte supprimé…) → redirige vers support@path2learn.com ou le chat en direct.
- Utilise des emojis modérément pour rendre la réponse chaleureuse.
PROMPT;

    // Prépare les messages avec le prompt système en premier
    $messages = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];

    // Ajoute l'historique (user + assistant)
    foreach ($history as $msg) {
        $messages[] = $msg;
    }

    $payload = [
        "model" => $this->model,
        "messages" => $messages,
        "temperature" => 0.7,         // plus bas = plus concentré / moins créatif
        "max_tokens" => 250,          // limite pour rester concis
        "top_p" => 0.9
    ];

    $response = $this->client->request("POST", $url, [
        "headers" => [
            "Authorization" => "Bearer " . $this->apiKey,
            "Content-Type" => "application/json"
        ],
        "json" => $payload,
        "timeout" => 60
    ]);

    $content = $response->getContent(false);
    $data = json_decode($content, true);

    if (isset($data['choices'][0]['message']['content'])) {
        return trim($data['choices'][0]['message']['content']);
    }

    throw new \Exception("No valid response from Groq: " . $content);
}
}