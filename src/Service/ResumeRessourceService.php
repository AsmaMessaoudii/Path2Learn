<?php
// src/Service/ResumeRessourceService.php

namespace App\Service;

use App\Entity\RessourcePedagogique;
use App\Entity\ResumeRessource;
use Doctrine\ORM\EntityManagerInterface;

class ResumeRessourceService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExtractionTexteService $extractionService,
        private ResumeService $resumeService
    ) {}

    /**
     * Analyser et résumer une ressource
     */
    public function analyserRessource(RessourcePedagogique $ressource): ResumeRessource
    {
        // Vérifier si un résumé existe déjà
        $existingResume = $this->entityManager
            ->getRepository(ResumeRessource::class)
            ->findOneBy(['ressource' => $ressource]);

        if ($existingResume) {
            return $existingResume;
        }

        // Créer un nouveau résumé
        $resumeRessource = new ResumeRessource();
        $resumeRessource->setRessource($ressource);
        $resumeRessource->setDateAnalyse(new \DateTime());

        // Extraire le texte selon le type
        $contenu = $this->extractionService->extraireTexte($ressource);
        
        if ($contenu) {
            // Cas où on a du texte (PDF, documents, etc.)
            $resumeRessource->setContenuTexte($contenu);
            $resumeRessource->setResumeCourt($this->resumeService->genererResumeCourt($contenu));
            $resumeRessource->setMotsCles($this->resumeService->extraireMotsCles($contenu, 10));
            $resumeRessource->setResumeDetaille($this->genererResumeDetaille($contenu));
            
            // Métadonnées
            $metadonnees = $this->extractionService->getMetadonnees($ressource);
            if (isset($metadonnees['pages'])) {
                $resumeRessource->setNombrePages($metadonnees['pages']);
            }
            
            $resumeRessource->setAnalyseComplete(true);
        } else {
            // Pas de contenu textuel - messages adaptés selon le type
            $type = $ressource->getType();
            
            switch ($type) {
                case 'Vidéo':
                    $resumeRessource->setResumeCourt("📹 Vidéo à visionner - " . $this->extraireTitreYouTube($ressource->getUrl()));
                    $resumeRessource->setResumeDetaille(
                        "Cette ressource est une vidéo. Pour en tirer le meilleur parti :\n\n" .
                        "1️⃣ Regardez la vidéo en entier\n" .
                        "2️⃣ Activez les sous-titres si disponibles\n" .
                        "3️⃣ Prenez des notes pendant le visionnage\n" .
                        "4️⃣ Regardez une seconde fois pour mieux comprendre\n" .
                        "5️⃣ Discutez du contenu avec vos camarades\n\n" .
                        "📌 Lien : " . $ressource->getUrl()
                    );
                    $resumeRessource->setMotsCles(['vidéo', 'tutoriel', 'cours']);
                    break;
                    
                case 'Lien':
                    $resumeRessource->setResumeCourt("🔗 Lien externe - " . $this->extraireNomDomaine($ressource->getUrl()));
                    $resumeRessource->setResumeDetaille(
                        "Cette ressource est un lien externe. Pour l'utiliser :\n\n" .
                        "1️⃣ Cliquez sur le lien pour accéder au contenu\n" .
                        "2️⃣ Explorez les informations sur le site\n" .
                        "3️⃣ Revenez partager ce que vous avez appris\n\n" .
                        "📌 Lien : " . $ressource->getUrl()
                    );
                    $resumeRessource->setMotsCles(['lien', 'ressource externe', 'web']);
                    break;
                    
                case 'Audio':
                    $resumeRessource->setResumeCourt("🎧 Fichier audio à écouter");
                    $resumeRessource->setResumeDetaille(
                        "Cette ressource est un fichier audio. Pour l'utiliser :\n\n" .
                        "1️⃣ Écoutez attentivement\n" .
                        "2️⃣ Prenez des notes\n" .
                        "3️⃣ Réécoutez si nécessaire"
                    );
                    $resumeRessource->setMotsCles(['audio', 'podcast', 'écoute']);
                    break;
                    
                case 'Image':
                    $resumeRessource->setResumeCourt("🖼️ Image illustrative");
                    $resumeRessource->setResumeDetaille(
                        "Cette ressource est une image. Observez-la attentivement pour comprendre les concepts visuels."
                    );
                    $resumeRessource->setMotsCles(['image', 'illustration', 'visuel']);
                    break;
                    
                default:
                    $resumeRessource->setResumeCourt("Cette ressource ne contient pas de texte analysable.");
                    $resumeRessource->setResumeDetaille(
                        "Aucun contenu textuel n'a pu être extrait automatiquement.\n" .
                        "Consultez la ressource directement pour plus d'informations."
                    );
                    $resumeRessource->setMotsCles([]);
            }
            
            $resumeRessource->setAnalyseComplete(false);
        }

        // Détecter la langue si on a du texte
        if ($contenu) {
            $resumeRessource->setLangue($this->detecterLangue($contenu));
        } else {
            $resumeRessource->setLangue('unknown');
        }

        $this->entityManager->persist($resumeRessource);
        $this->entityManager->flush();

        return $resumeRessource;
    }

    /**
     * Extraire le titre d'une vidéo YouTube depuis l'URL
     */
    private function extraireTitreYouTube(?string $url): string
    {
        if (!$url) return 'YouTube';
        
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            return 'Vidéo YouTube';
        }
        
        return 'Vidéo';
    }

    /**
     * Extraire le nom de domaine d'une URL
     */
    private function extraireNomDomaine(?string $url): string
    {
        if (!$url) return 'site web';
        
        $parsed = parse_url($url);
        $domaine = $parsed['host'] ?? $url;
        
        // Enlever www. si présent
        $domaine = preg_replace('/^www\./', '', $domaine);
        
        return $domaine;
    }

    /**
     * Générer un résumé détaillé
     */
    private function genererResumeDetaille(string $texte): string
    {
        // Nettoyer le texte
        $texte = preg_replace('/\s+/', ' ', $texte);
        
        // Prendre les 5 premières phrases ou 1000 caractères
        $phrases = preg_split('/(?<=[.?!])\s+/', $texte, 6);
        
        if (count($phrases) > 5) {
            return implode(' ', array_slice($phrases, 0, 5));
        }
        
        if (strlen($texte) > 1000) {
            return substr($texte, 0, 1000) . '...';
        }
        
        return $texte;
    }

    /**
     * Détecter la langue (simplifié)
     */
    private function detecterLangue(string $texte): string
    {
        if (empty($texte)) return 'unknown';
        
        $motsFrancais = ['le', 'la', 'les', 'un', 'une', 'des', 'et', 'ou', 'mais', 'donc', 'car', 'pour', 'dans'];
        $motsAnglais = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'for', 'with', 'by'];
        
        $texte = strtolower($texte);
        $mots = str_word_count($texte, 1);
        
        $scoreFr = 0;
        $scoreEn = 0;
        
        foreach ($mots as $mot) {
            if (in_array($mot, $motsFrancais)) $scoreFr++;
            if (in_array($mot, $motsAnglais)) $scoreEn++;
        }
        
        if ($scoreFr > $scoreEn) return 'fr';
        if ($scoreEn > $scoreFr) return 'en';
        return 'unknown';
    }

    /**
     * Analyser toutes les ressources d'un cours
     */
    public function analyserRessourcesCours($cours): array
    {
        $resultats = [];
        
        foreach ($cours->getRessourcePedagogiques() as $ressource) {
            $resultats[] = $this->analyserRessource($ressource);
        }
        
        return $resultats;
    }

    /**
     * Générer un résumé global de toutes les ressources
     */
    public function genererResumeGlobal($cours): array
    {
        $ressources = $cours->getRessourcePedagogiques();
        $tousTextes = [];
        $stats = [
            'total' => $ressources->count(),
            'analysees' => 0,
            'types' => []
        ];
        
        foreach ($ressources as $ressource) {
            $resume = $this->analyserRessource($ressource);
            
            if ($resume->isAnalyseComplete() && $resume->getContenuTexte()) {
                $stats['analysees']++;
                $tousTextes[] = $resume->getContenuTexte();
            }
            
            // Compter par type
            $type = $ressource->getType();
            if (!isset($stats['types'][$type])) {
                $stats['types'][$type] = 0;
            }
            $stats['types'][$type]++;
        }
        
        // Générer un résumé global si on a du texte
        if (!empty($tousTextes)) {
            $texteGlobal = implode(' ', $tousTextes);
            $resumeGlobal = $this->resumeService->genererResumeCourt($texteGlobal);
            $motsClesGlobaux = $this->resumeService->extraireMotsCles($texteGlobal, 15);
        } else {
            $resumeGlobal = "Ce cours contient " . $ressources->count() . " ressources de différents types (vidéos, liens, etc.). Consultez chaque ressource individuellement.";
            $motsClesGlobaux = ['multimédia', 'ressources', 'cours'];
        }
        
        return [
            'statistiques' => $stats,
            'resumeGlobal' => $resumeGlobal,
            'motsClesGlobaux' => $motsClesGlobaux,
            'ressourcesDetail' => $ressources
        ];
    }
}