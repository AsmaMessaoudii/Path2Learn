<?php
// src/Service/ChatbotService.php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\User;
use App\Repository\ChatMessageRepository;
use App\Repository\RessourcePedagogiqueRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Smalot\PdfParser\Parser as PdfParser;

class ChatbotService
{
    private string $model = 'gemini-2.5-flash';
    private ?string $apiKey;
    private string $uploadDirectory;  // ✅ propriété ajoutée

    public function __construct(
        private HttpClientInterface $httpClient,
        private ChatMessageRepository $chatMessageRepository,
        private RessourcePedagogiqueRepository $ressourceRepository,
        string $geminiApiKey,
        string $uploadDirectory   // ✅ paramètre ajouté
    ) {
        $this->apiKey          = $geminiApiKey;
        $this->uploadDirectory = $uploadDirectory;  // ✅ assignation ajoutée
    }

    public function answer(string $question, Cours $cours, User $user): string
    {
        $context = $this->buildCourseContext($cours);

        $history = $this->chatMessageRepository->findByCoursAndUser($cours, $user, 4);
        $historyText = '';
        foreach ($history as $msg) {
            $historyText .= "Étudiant: " . $msg->getQuestion() . "\n";
            $historyText .= "Assistant: " . $msg->getAnswer() . "\n\n";
        }

        $prompt = <<<PROMPT
        Tu es un assistant pédagogique expert intégré dans une plateforme e-learning.
        Réponds en français, de façon claire, directe et structurée.
        Base-toi UNIQUEMENT sur le contenu du cours fourni.
        Si la réponse est dans le cours, donne-la complètement avec des exemples si possible.
        Ne commence PAS chaque réponse par "Bonjour", réponds directement à la question.
        
        === CONTENU DU COURS ===
        {$context}
        === FIN DU CONTENU ===
        
        === HISTORIQUE ===
        {$historyText}
        === FIN HISTORIQUE ===
        
        Question : {$question}
        PROMPT;

        $response = $this->httpClient->request(
            'POST',
            'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . $this->apiKey,
            [
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.7,
                        'maxOutputTokens' => 800,
                        'topP'            => 0.9,
                    ]
                ],
                'timeout' => 30,
            ]
        );

        $data   = $response->toArray();
        $answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$answer) {
            throw new \RuntimeException('Pas de réponse de Gemini');
        }

        return trim($answer);
    }

    private function buildCourseContext(Cours $cours): string
{
    $parts = [];
    $parts[] = "Titre : " . $cours->getTitre();
    $parts[] = "Matière : " . ($cours->getMatiere() ?? 'N/A');
    $parts[] = "Niveau : " . ($cours->getNiveau() ?? 'N/A');

    $desc = strip_tags($cours->getDescription() ?? '');
    $parts[] = "Description : " . substr($desc, 0, 300);

    $ressources = $this->ressourceRepository->findBy(['cours' => $cours]);
    foreach ($ressources as $ressource) {
        $titre = $ressource->getTitre() ?? $ressource->getFileName() ?? 'Ressource';
        $text  = $this->extractResourceContent($ressource);
        if ($text) {
            $parts[] = "\n--- Contenu de la ressource : {$titre} ---\n{$text}";
        }
    }

    return implode("\n", $parts);
}
    private function extractResourceContent($ressource): ?string
    {
        if (!$ressource->getFileName()) {
            return $ressource->getUrl() ? "URL: " . $ressource->getUrl() : null;
        }

        $filePath = $this->uploadDirectory . '/' . $ressource->getFileName();
        $ext      = strtolower(pathinfo($ressource->getFileName(), PATHINFO_EXTENSION));

        if (!file_exists($filePath)) return null;

        try {
            $text = match($ext) {
                'pdf'       => (new PdfParser())->parseFile($filePath)->getText(),
                'txt', 'md' => file_get_contents($filePath),
                'docx'      => $this->extractWord($filePath),
                default     => null
            };

            if ($text && strlen($text) > 3000) {
                $text = substr($text, 0, 3000) . '…';
            }

            return $text ? preg_replace('/\s+/', ' ', trim($text)) : null;

        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractWord(string $filePath): string
    {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }
        return $text;
    }
}