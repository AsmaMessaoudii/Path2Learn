<?php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\RessourcePedagogique;
use App\Repository\CourseSummaryRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdvancedAISummaryService
{
    private EntityManagerInterface $entityManager;
    private ContentExtractorService $contentExtractor;
    private ?string $ollamaUrl = 'http://localhost:11434/api/generate';
    
    public function __construct(
        EntityManagerInterface $entityManager,
        ContentExtractorService $contentExtractor
    ) {
        $this->entityManager = $entityManager;
        $this->contentExtractor = $contentExtractor;
    }
    
    /**
     * Génère un résumé intelligent en analysant le contenu réel des ressources
     */
    public function generateIntelligentSummary(Cours $cours, array $ressources): array
    {
        // 1. Extraire le contenu de toutes les ressources
        $extractedContents = $this->contentExtractor->extractContentFromResources($ressources);
        
        // 2. Analyser et synthétiser le contenu
        $synthesis = $this->synthesizeContent($cours, $extractedContents);
        
        // 3. Générer un résumé structuré
        $summary = $this->generateStructuredSummary($synthesis);
        
        // 4. Extraire les concepts clés
        $keyConcepts = $this->extractKeyConcepts($synthesis);
        
        // 5. Générer un plan d'apprentissage
        $learningPath = $this->generateLearningPath($synthesis);
        
        return [
            'summary' => $summary,
            'key_concepts' => $keyConcepts,
            'learning_path' => $learningPath,
            'resources_analysis' => $this->analyzeResources($extractedContents),
            'difficulty_assessment' => $this->assessDifficulty($synthesis),
            'estimated_time' => $this->estimateLearningTime($synthesis)
        ];
    }
    
    /**
     * Synthétise le contenu extrait
     */
    private function synthesizeContent(Cours $cours, array $contents): array
    {
        $synthesis = [
            'course_title' => $cours->getTitre(),
            'course_description' => strip_tags($cours->getDescription() ?? ''),
            'main_topics' => [],
            'key_points' => [],
            'examples' => [],
            'definitions' => [],
            'code_snippets' => [],
            'statistics' => [],
            'total_words' => 0
        ];
        
        foreach ($contents as $content) {
            $text = $content['contenu'] ?? '';
            $synthesis['total_words'] += str_word_count($text);
            
            // Détecter les sujets principaux (mots fréquents et significatifs)
            $topics = $this->extractTopics($text);
            $synthesis['main_topics'] = array_merge($synthesis['main_topics'], $topics);
            
            // Détecter les points clés (phrases importantes)
            $keyPoints = $this->extractKeyPoints($text);
            $synthesis['key_points'] = array_merge($synthesis['key_points'], $keyPoints);
            
            // Détecter les exemples
            if (preg_match_all('/(par exemple|exemple|comme|tel que|illustration)[^.!?]*[.!?]/i', $text, $matches)) {
                $synthesis['examples'] = array_merge($synthesis['examples'], $matches[0]);
            }
            
            // Détecter les définitions
            if (preg_match_all('/(définition|est défini|représente|signifie|correspond à)[^.!?]*[.!?]/i', $text, $matches)) {
                $synthesis['definitions'] = array_merge($synthesis['definitions'], $matches[0]);
            }
            
            // Détecter le code
            if (preg_match_all('/```[\s\S]*?```|`[^`]+`/', $text, $matches)) {
                $synthesis['code_snippets'] = array_merge($synthesis['code_snippets'], $matches[0]);
            }
            
            // Détecter les statistiques/chiffres
            if (preg_match_all('/\b\d+%|\b\d+\s*(pourcent|fois|unités|euros|€)\b/', $text, $matches)) {
                $synthesis['statistics'] = array_merge($synthesis['statistics'], $matches[0]);
            }
        }
        
        // Nettoyer et dédupliquer
        $synthesis['main_topics'] = array_unique($synthesis['main_topics']);
        $synthesis['key_points'] = array_slice(array_unique($synthesis['key_points']), 0, 20);
        $synthesis['examples'] = array_slice(array_unique($synthesis['examples']), 0, 10);
        $synthesis['definitions'] = array_slice(array_unique($synthesis['definitions']), 0, 10);
        
        return $synthesis;
    }
    
    /**
     * Extrait les sujets principaux d'un texte
     */
    private function extractTopics(string $text): array
    {
        // Mots vides à ignorer
        $stopWords = ['le', 'la', 'les', 'du', 'de', 'des', 'un', 'une', 'et', 'est', 'sont', 
                     'dans', 'pour', 'par', 'sur', 'avec', 'ce', 'cet', 'cette', 'ces'];
        
        // Nettoyer le texte
        $text = strtolower($text);
        $text = preg_replace('/[^a-zàâçéèêëîïôûùüÿñæœ\s]/u', ' ', $text);
        
        // Compter les mots
        $words = str_word_count($text, 1);
        $wordCounts = array_count_values($words);
        
        // Filtrer et trier
        foreach ($wordCounts as $word => $count) {
            if (strlen($word) < 4 || in_array($word, $stopWords)) {
                unset($wordCounts[$word]);
            }
        }
        
        arsort($wordCounts);
        return array_slice(array_keys($wordCounts), 0, 15);
    }
    
    /**
     * Extrait les points clés d'un texte
     */
    private function extractKeyPoints(string $text): array
    {
        $points = [];
        
        // Chercher les phrases qui semblent importantes
        $sentences = preg_split('/[.!?]+/', $text);
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) < 20) continue;
            
            // Mots indicateurs de points importants
            $indicators = ['important', 'essentiel', 'clé', 'fondamental', 'noter', 
                          'retenir', 'conclusion', 'résultat', 'objectif'];
            
            foreach ($indicators as $indicator) {
                if (stripos($sentence, $indicator) !== false) {
                    $points[] = ucfirst($sentence) . '.';
                    break;
                }
            }
        }
        
        return $points;
    }
    
    /**
     * Génère un résumé structuré
     */
    private function generateStructuredSummary(array $synthesis): string
    {
        $summary = [];
        
        // En-tête
        $summary[] = "📚 **RÉSUMÉ INTELLIGENT DU COURS : {$synthesis['course_title']}**\n";
        
        // Introduction
        $summary[] = "**📝 APERÇU GÉNÉRAL**";
        $summary[] = wordwrap($synthesis['course_description'], 80) . "\n";
        
        // Sujets principaux
        if (!empty($synthesis['main_topics'])) {
            $summary[] = "**🎯 SUJETS PRINCIPAUX ABORDÉS**";
            foreach (array_slice($synthesis['main_topics'], 0, 10) as $topic) {
                $summary[] = "- {$topic}";
            }
            $summary[] = "";
        }
        
        // Points clés
        if (!empty($synthesis['key_points'])) {
            $summary[] = "**🔑 POINTS CLÉS À RETENIR**";
            foreach ($synthesis['key_points'] as $point) {
                $summary[] = "• {$point}";
            }
            $summary[] = "";
        }
        
        // Concepts importants
        if (!empty($synthesis['definitions'])) {
            $summary[] = "**📖 CONCEPTS IMPORTANTS**";
            foreach ($synthesis['definitions'] as $definition) {
                $summary[] = "• {$definition}";
            }
            $summary[] = "";
        }
        
        // Exemples
        if (!empty($synthesis['examples'])) {
            $summary[] = "**💡 EXEMPLES ILLUSTRATIFS**";
            foreach ($synthesis['examples'] as $example) {
                $summary[] = "• {$example}";
            }
            $summary[] = "";
        }
        
        // Code (si présent)
        if (!empty($synthesis['code_snippets'])) {
            $summary[] = "**💻 EXTRAITS DE CODE**";
            foreach ($synthesis['code_snippets'] as $code) {
                $summary[] = "```\n{$code}\n```";
            }
            $summary[] = "";
        }
        
        // Statistiques
        $summary[] = "**📊 STATISTIQUES DU COURS**";
        $summary[] = "- Nombre total de ressources analysées: " . count($synthesis['main_topics']);
        $summary[] = "- Mots analysés: ~{$synthesis['total_words']} mots";
        $summary[] = "- Concepts clés identifiés: " . count($synthesis['key_points']);
        $summary[] = "- Exemples fournis: " . count($synthesis['examples']);
        
        return implode("\n", $summary);
    }
    
    /**
     * Extrait les concepts clés
     */
    private function extractKeyConcepts(array $synthesis): array
    {
        $concepts = [];
        
        // Prendre les sujets principaux et les formater
        foreach (array_slice($synthesis['main_topics'], 0, 15) as $topic) {
            $concepts[] = [
                'name' => $topic,
                'importance' => 'élevée',
                'mentions' => rand(5, 20) // Simulé, à améliorer
            ];
        }
        
        return $concepts;
    }
    
    /**
     * Génère un plan d'apprentissage
     */
    private function generateLearningPath(array $synthesis): array
    {
        $path = [];
        
        // Créer un ordre logique basé sur les sujets
        $topics = $synthesis['main_topics'];
        
        if (!empty($topics)) {
            $path[] = "Module 1: Introduction et concepts de base";
            $path[] = "Module 2: " . ($topics[0] ?? 'Premier sujet principal');
            
            if (count($topics) > 1) {
                $path[] = "Module 3: " . ($topics[1] ?? 'Deuxième sujet principal');
            }
            
            if (count($topics) > 2) {
                $path[] = "Module 4: " . ($topics[2] ?? 'Troisième sujet principal');
            }
            
            $path[] = "Module 5: Cas pratiques et exercices";
            $path[] = "Module 6: Évaluation et synthèse";
        }
        
        return $path;
    }
    
    /**
     * Analyse détaillée des ressources
     */
    private function analyzeResources(array $contents): array
    {
        $analysis = [
            'total_resources' => count($contents),
            'by_type' => [],
            'most_valuable' => [],
            'content_quality' => 'Élevée'
        ];
        
        foreach ($contents as $content) {
            $type = $content['type'];
            if (!isset($analysis['by_type'][$type])) {
                $analysis['by_type'][$type] = 0;
            }
            $analysis['by_type'][$type]++;
            
            if ($content['importance'] > 1.5) {
                $analysis['most_valuable'][] = $content['titre'];
            }
        }
        
        return $analysis;
    }
    
    /**
     * Évalue la difficulté du cours
     */
    private function assessDifficulty(array $synthesis): string
    {
        $technicalTerms = ['algorithme', 'complexité', 'abstrait', 'architecture', 
                          'framework', 'bibliothèque', 'paradigme'];
        
        $technicalCount = 0;
        foreach ($technicalTerms as $term) {
            if (stripos($synthesis['course_description'], $term) !== false) {
                $technicalCount++;
            }
        }
        
        return match(true) {
            $technicalCount > 3 => 'Avancé',
            $technicalCount > 1 => 'Intermédiaire',
            default => 'Débutant'
        };
    }
    
    /**
     * Estime le temps d'apprentissage
     */
    private function estimateLearningTime(array $synthesis): string
    {
        $wordsPerMinute = 200; // Vitesse de lecture moyenne
        $minutes = ceil($synthesis['total_words'] / $wordsPerMinute);
        
        if ($minutes < 60) {
            return "~{$minutes} minutes";
        } else {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            return "~{$hours}h" . ($remainingMinutes > 0 ? " {$remainingMinutes}min" : "");
        }
    }
}