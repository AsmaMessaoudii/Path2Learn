<?php
// src/Service/AISummaryService.php

namespace App\Service;

use App\Entity\Cours;
use App\Repository\CourseSummaryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Smalot\PdfParser\Parser as PdfParser;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AISummaryService
{
    private EntityManagerInterface $entityManager;
    private CourseSummaryRepository $summaryRepository;
    private HttpClientInterface $httpClient;
    private string $uploadDirectory;
    private ?string $apiKey = null;

    // ✅ CORRECTION : modèle disponible dans votre compte
    private string $model = 'gemini-2.5-flash';

    public function __construct(
        EntityManagerInterface $entityManager,
        CourseSummaryRepository $summaryRepository,
        HttpClientInterface $httpClient,
        string $uploadDirectory
    ) {
        $this->entityManager     = $entityManager;
        $this->summaryRepository = $summaryRepository;
        $this->httpClient        = $httpClient;
        $this->uploadDirectory   = $uploadDirectory;
        $this->apiKey            = $_ENV['GEMINI_API_KEY'] ?? null;
    }

    /**
     * Point d'entrée principal : génère un résumé pédagogique structuré
     */
    public function generateSummary(Cours $cours, array $ressources, string $type = 'standard'): array
    {
        $summary  = null;
        $keywords = '';

        if ($this->apiKey) {
            try {
                $result   = $this->callGeminiAPI($cours, $ressources);
                $summary  = $result['summary'];
                $keywords = $result['keywords'];
            } catch (\Exception $e) {
                error_log('Erreur Gemini: ' . $e->getMessage());
                $summary = null;
            }
        }

        // Fallback statique si Gemini indisponible
        if (!$summary) {
            $summary  = $this->generateBasicSummary($cours, $ressources);
            $keywords = $this->extractKeywordsNaive($summary);
        }

        return [
            'summary'  => $summary,
            'keywords' => $keywords,
            'stats'    => [
                'total_ressources' => count($ressources),
                'course_duration'  => $cours->getDuree(),
                'course_level'     => $cours->getNiveau(),
                'generated_at'     => new \DateTime()
            ]
        ];
    }

    /**
     * Appelle Gemini avec le vrai contenu des ressources
     */
    private function callGeminiAPI(Cours $cours, array $ressources): array
    {
        $titre       = $cours->getTitre();
        $description = strip_tags($cours->getDescription() ?? '');
        $matiere     = $cours->getMatiere() ?? 'Général';
        $niveau      = $cours->getNiveau() ?? 'Tous niveaux';
        $duree       = $cours->getDuree() ?? 0;

        $ressourcesContent = $this->buildResourcesContent($ressources);

        $prompt = <<<PROMPT
Tu es un assistant pédagogique expert.
Voici le contenu COMPLET d'un cours : description + texte extrait des ressources (PDFs, documents, etc.).

Génère DEUX choses en JSON valide avec exactement ces deux champs :
1. "summary" : le résumé pédagogique structuré (chaîne de texte)
2. "keywords" : liste de 10 à 15 mots-clés importants du cours, séparés par des virgules

Le résumé doit suivre EXACTEMENT ce format (gardez les emojis) :

---
📚 **Titre du cours** : {$titre}

🎯 **Objectif général** : [Ce que l'apprenant va apprendre, en une phrase précise]

📝 **Résumé global** : [3 à 5 phrases résumant l'essentiel du cours ET le contenu des ressources fournies]

🗂️ **Points clés abordés** :
1. [Point clé tiré du contenu des ressources]
2. [Point clé tiré du contenu des ressources]
3. [Point clé tiré du contenu des ressources]
4. [Point clé tiré du contenu des ressources]
5. [Point clé tiré du contenu des ressources]

💡 **Concepts importants à retenir** :
- [Concept 1 extrait des ressources] : [explication courte]
- [Concept 2 extrait des ressources] : [explication courte]
- [Concept 3 extrait des ressources] : [explication courte]

✅ **Ce que l'apprenant est capable de faire après ce cours** :
- [Compétence 1 basée sur le contenu]
- [Compétence 2 basée sur le contenu]
- [Compétence 3 basée sur le contenu]
---

CONTENU DU COURS :
**Titre** : {$titre}
**Matière** : {$matiere}
**Niveau** : {$niveau}
**Durée** : {$duree} minutes
**Description** : {$description}

CONTENU DES RESSOURCES PÉDAGOGIQUES (texte extrait des fichiers) :
{$ressourcesContent}

Réponds UNIQUEMENT avec un objet JSON valide, sans backticks, sans texte avant ou après.
Exemple de format attendu :
{"summary":"📚 **Titre du cours**...","keywords":"algèbre, équations, fonctions, dérivées, ..."}
PROMPT;

        // ✅ CORRECTION : bon modèle + bonne URL
        $response = $this->httpClient->request(
            'POST',
            'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . $this->apiKey,
            [
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.6,
                        'maxOutputTokens' => 3000,
                        'topP'            => 0.9,
                    ]
                ],
                // ✅ Timeout pour éviter les blocages
                'timeout' => 30,
            ]
        );

        $statusCode = $response->getStatusCode();

        // ✅ Gestion du rate limit 429
        if ($statusCode === 429) {
            throw new \RuntimeException('Quota API Gemini dépassé (429). Réessayez dans quelques secondes.');
        }

        $data = $response->toArray();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            throw new \RuntimeException('Réponse vide de Gemini');
        }

        // Nettoyer d'éventuels blocs markdown ```json ... ```
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        $decoded = json_decode($text, true);

        if (!is_array($decoded) || empty($decoded['summary'])) {
            // Gemini a répondu en texte brut malgré l'instruction
            return [
                'summary'  => trim($text),
                'keywords' => ''
            ];
        }

        return [
            'summary'  => trim($decoded['summary']),
            'keywords' => trim($decoded['keywords'] ?? '')
        ];
    }

    /**
     * Extrait et formate le contenu de toutes les ressources pour le prompt.
     */
    private function buildResourcesContent(array $ressources): string
    {
        if (empty($ressources)) {
            return "Aucune ressource disponible pour ce cours.";
        }

        $parts = [];

        foreach ($ressources as $ressource) {
            $titre = $ressource->getTitre() ?? $ressource->getFileName() ?? 'Ressource';
            $type  = $ressource->getType() ?? 'Fichier';
            $text  = null;

            if ($ressource->getFileName()) {
                $filePath  = $this->uploadDirectory . '/' . $ressource->getFileName();
                $extension = strtolower(pathinfo($ressource->getFileName(), PATHINFO_EXTENSION));

                if (file_exists($filePath)) {
                    try {
                        $text = match($extension) {
                            'pdf'         => $this->extractPdf($filePath),
                            'txt', 'md'   => file_get_contents($filePath),
                            'docx', 'doc' => $this->extractWord($filePath),
                            default       => null
                        };
                    } catch (\Exception $e) {
                        error_log('Erreur extraction ressource: ' . $e->getMessage());
                        $text = null;
                    }
                }

                if ($text && strlen($text) > 4000) {
                    $text = substr($text, 0, 4000) . '…';
                }

                if ($text) {
                    $text = preg_replace('/\s+/', ' ', $text);
                    $text = trim($text);
                }
            }

            if (!$text && $ressource->getUrl()) {
                $type = 'Lien externe';
                $text = $ressource->getUrl();
            }

            if ($text) {
                $parts[] = "### [{$type}] {$titre}\n{$text}";
            } else {
                $parts[] = "- [{$type}] {$titre} (contenu non extractible)";
            }
        }

        return implode("\n\n", $parts);
    }

    private function extractPdf(string $filePath): string
    {
        $parser = new PdfParser();
        $pdf    = $parser->parseFile($filePath);
        return $pdf->getText();
    }

    private function extractWord(string $filePath): string
    {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
        $text    = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }
        return $text;
    }

    private function generateBasicSummary(Cours $cours, array $ressources): string
    {
        $titre   = $cours->getTitre();
        $matiere = $cours->getMatiere() ?? 'Général';
        $niveau  = $cours->getNiveau() ?? 'Tous niveaux';
        $duree   = $cours->getDuree() ?? 0;
        $desc    = strip_tags($cours->getDescription() ?? "Ce cours couvre les notions fondamentales de {$matiere}.");

        $nbPdf    = count(array_filter($ressources, fn($r) => $r->getFileName() && str_ends_with(strtolower($r->getFileName()), '.pdf')));
        $nbVideos = count(array_filter($ressources, fn($r) => $r->getFileName() && in_array(strtolower(pathinfo($r->getFileName(), PATHINFO_EXTENSION)), ['mp4','mov','avi','mkv'])));
        $nbLinks  = count(array_filter($ressources, fn($r) => $r->getUrl()));

        $lines = [
            "📚 **Titre du cours** : {$titre}\n",
            "🎯 **Objectif général** : Acquérir des connaissances en {$matiere} au niveau {$niveau}.\n",
            "📝 **Résumé global** : {$desc}\n",
            "🗂️ **Points clés abordés** :",
            "1. Matière : {$matiere}",
            "2. Niveau : {$niveau}",
            "3. Durée estimée : {$duree} minutes",
            "4. Nombre total de ressources : " . count($ressources),
            "5. Ressources textuelles (PDF) : {$nbPdf}\n",
            "💡 **Concepts importants à retenir** :",
            "- Contenu : se reporter aux ressources du cours",
            $nbPdf    ? "- Documents PDF ({$nbPdf}) : sources principales à étudier" : "",
            $nbVideos ? "- Vidéos ({$nbVideos}) : supports visuels à visionner" : "",
            $nbLinks  ? "- Liens ({$nbLinks}) : ressources complémentaires en ligne" : "",
            "\n✅ **Ce que l'apprenant est capable de faire après ce cours** :",
            "- Comprendre les concepts fondamentaux de {$matiere}",
            "- Exploiter les ressources mises à disposition",
            "- Appliquer les connaissances dans des situations pratiques",
        ];

        return implode("\n", array_filter($lines));
    }

    private function extractKeywordsNaive(string $summary): string
    {
        $stopWords = ['le','la','les','du','de','des','un','une','et','est','sont','dans',
                      'pour','par','sur','avec','ce','cet','cette','ces','au','aux','que','qui'];

        $text  = strtolower(strip_tags($summary));
        $text  = preg_replace('/[^a-zA-ZÀ-ÿ\s]/u', ' ', $text);
        $words = array_count_values(str_word_count($text, 1));

        foreach ($words as $word => $count) {
            if (strlen($word) < 4 || in_array($word, $stopWords)) {
                unset($words[$word]);
            }
        }

        arsort($words);
        return implode(', ', array_slice(array_keys($words), 0, 15));
    }

    public function generateAISummary(Cours $cours, array $ressources): array
    {
        return $this->generateSummary($cours, $ressources, 'ai');
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }
}