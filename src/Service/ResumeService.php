<?php
// src/Service/ResumeService.php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\RessourcePedagogique;

class ResumeService
{
    /**
     * Générer un résumé du cours avec liens
     */
    public function genererResume(Cours $cours, string $longueur = 'court'): array
    {
        $description = $cours->getDescription();
        
        return [
            'court' => $this->genererResumeCourt($description),
            'motsCles' => $this->extraireMotsCles($description),
            'statistiques' => $this->calculerStatistiques($cours),
            'ressourcesImportantes' => $this->trouverRessourcesImportantes($cours),
            'ressourcesAvecLiens' => $this->genererLiensRessources($cours),
            'questionsRevision' => $this->genererQuestionsRevision($cours),
            'questionsReponses' => $this->genererQuestionsAvecReponses($cours)
        ];
    }

    /**
     * Résumé court (2-3 phrases)
     * MAINTENANT PUBLIC
     */
    public function genererResumeCourt(string $texte): string
    {
        // Nettoyer le texte
        $texte = strip_tags($texte);
        $texte = preg_replace('/\s+/', ' ', $texte);
        
        // Prendre les premières phrases
        $phrases = preg_split('/(?<=[.?!])\s+/', $texte, 4);
        
        if (count($phrases) > 2) {
            return implode(' ', array_slice($phrases, 0, 2));
        }
        
        // Si pas assez de phrases, prendre les 200 premiers caractères
        if (strlen($texte) > 200) {
            return substr($texte, 0, 200) . '...';
        }
        
        return $texte;
    }

    /**
     * Extraire les mots-clés
     * MAINTENANT PUBLIC
     */
    public function extraireMotsCles(string $texte, int $nbMots = 5): array
    {
        // Nettoyer le texte
        $texte = strtolower(strip_tags($texte));
        
        // Mots à exclure
        $motsExclus = [
            'le', 'la', 'les', 'un', 'une', 'des', 'et', 'ou', 'mais', 'donc',
            'car', 'pour', 'dans', 'sur', 'avec', 'sans', 'par', 'est', 'sont',
            'cet', 'cette', 'ces', 'mon', 'ton', 'son', 'notre', 'votre', 'leur',
            'je', 'tu', 'il', 'elle', 'nous', 'vous', 'ils', 'elles', 'ce', 'c\'est'
        ];
        
        // Extraire tous les mots
        preg_match_all('/\b(\w+)\b/u', $texte, $matches);
        $mots = $matches[1];
        
        // Filtrer et compter
        $motsFiltres = array_filter($mots, function($mot) use ($motsExclus) {
            return strlen($mot) > 3 && !in_array($mot, $motsExclus);
        });
        
        $frequences = array_count_values($motsFiltres);
        arsort($frequences);
        
        // Retourner les plus fréquents
        return array_slice(array_keys($frequences), 0, $nbMots);
    }

    /**
     * Calculer les statistiques du cours
     * (peut rester private car utilisé seulement dans cette classe)
     */
    private function calculerStatistiques(Cours $cours): array
    {
        $ressources = $cours->getRessourcePedagogiques();
        
        $stats = [
            'totalRessources' => $ressources->count(),
            'typesRessources' => [],
            'dureeTotale' => $cours->getDuree(),
            'niveau' => $cours->getNiveau(),
            'matiere' => $cours->getMatiere()
        ];
        
        // Compter par type
        foreach ($ressources as $ressource) {
            $type = $ressource->getType();
            if (!isset($stats['typesRessources'][$type])) {
                $stats['typesRessources'][$type] = 0;
            }
            $stats['typesRessources'][$type]++;
        }
        
        return $stats;
    }

    /**
     * Trouver les ressources importantes
     */
    private function trouverRessourcesImportantes(Cours $cours): array
    {
        $ressources = $cours->getRessourcePedagogiques()->toArray();
        
        // Trier par type (les PDF et vidéos en premier)
        usort($ressources, function($a, $b) {
            $prioriteA = $this->getPrioriteRessource($a);
            $prioriteB = $this->getPrioriteRessource($b);
            return $prioriteB - $prioriteA;
        });
        
        return array_slice($ressources, 0, 3);
    }

    /**
     * Priorité des ressources
     */
    private function getPrioriteRessource(RessourcePedagogique $ressource): int
    {
        $priorites = [
            'PDF' => 5,
            'Vidéo' => 4,
            'Présentation' => 3,
            'Document' => 2,
            'Lien' => 1
        ];
        
        return $priorites[$ressource->getType()] ?? 0;
    }

    /**
     * Générer des liens cliquables vers les ressources
     */
    private function genererLiensRessources(Cours $cours): array
    {
        $ressources = [];
        
        foreach ($cours->getRessourcePedagogiques() as $ressource) {
            $lien = '#';
            $typeLien = 'interne';
            
            // Déterminer le type de lien
            if ($ressource->getUrl()) {
                $lien = $ressource->getUrl();
                $typeLien = 'externe';
            } elseif ($ressource->getFileName()) {
                $lien = $ressource->getFileUrl();
                $typeLien = 'fichier';
            }
            
            $ressources[] = [
                'id' => $ressource->getId(),
                'titre' => $ressource->getTitre(),
                'type' => $ressource->getType(),
                'lien' => $lien,
                'typeLien' => $typeLien,
                'icone' => $ressource->getFileIcon(),
                'description' => $this->genererDescriptionRessource($ressource)
            ];
        }
        
        return $ressources;
    }

    /**
     * Générer une description pour la ressource
     */
    private function genererDescriptionRessource(RessourcePedagogique $ressource): string
    {
        $descriptions = [
            'PDF' => 'Document PDF à consulter ou télécharger',
            'Vidéo' => 'Vidéo explicative à visionner',
            'Lien' => 'Lien externe vers une ressource complémentaire',
            'Document' => 'Document texte à étudier',
            'Présentation' => 'Diapositives du cours',
            'Audio' => 'Fichier audio à écouter',
            'Image' => 'Image illustrative',
            'Exercice' => 'Exercice pratique à réaliser'
        ];
        
        return $descriptions[$ressource->getType()] ?? 'Ressource pédagogique';
    }

    /**
     * Générer des questions avec réponses
     */
    private function genererQuestionsAvecReponses(Cours $cours): array
    {
        $titre = $cours->getTitre();
        $description = $cours->getDescription();
        $motsCles = $this->extraireMotsCles($description, 5);
        
        $questions = [];
        
        // Question sur le titre
        $questions[] = [
            'question' => "Qu'est-ce que le cours '{$titre}' ?",
            'reponse' => "Ce cours aborde le thème de {$cours->getMatiere()} au niveau {$cours->getNiveau()}. Il dure {$cours->getDuree()} minutes et contient " . $cours->getRessourcePedagogiques()->count() . " ressources.",
            'categorie' => 'general'
        ];
        
        // Questions sur les concepts clés
        foreach ($motsCles as $index => $mot) {
            if ($index < 3) { // Limiter à 3 mots-clés
                $questions[] = [
                    'question' => "Pouvez-vous expliquer le concept de '{$mot}' ?",
                    'reponse' => "Le concept '{$mot}' est un élément important de ce cours. Pour bien le comprendre, consultez les ressources associées et pratiquez régulièrement.",
                    'categorie' => 'concept'
                ];
            }
        }
        
        // Question sur les ressources
        $questions[] = [
            'question' => "Quelles sont les ressources disponibles dans ce cours ?",
            'reponse' => $this->genererListeRessourcesText($cours),
            'categorie' => 'ressource'
        ];
        
        // Question pratique
        $questions[] = [
            'question' => "Comment appliquer les notions vues dans ce cours ?",
            'reponse' => "Pour appliquer ces notions :\n1) Révisez régulièrement les concepts clés\n2) Faites les exercices proposés\n3) Consultez les ressources complémentaires\n4) Pratiquez avec des projets personnels",
            'categorie' => 'pratique'
        ];
        
        return $questions;
    }

    /**
     * Générer la liste des ressources en texte
     */
    private function genererListeRessourcesText(Cours $cours): string
    {
        $ressources = $cours->getRessourcePedagogiques();
        $texte = "Ce cours contient {$ressources->count()} ressources :\n\n";
        
        $compteur = 1;
        foreach ($ressources as $ressource) {
            $texte .= "{$compteur}. {$ressource->getTitre()} ({$ressource->getType()})\n";
            $compteur++;
        }
        
        return $texte;
    }

    /**
     * Générer des questions de révision
     */
    private function genererQuestionsRevision(Cours $cours): array
    {
        $titre = $cours->getTitre();
        $description = $cours->getDescription();
        $motsCles = $this->extraireMotsCles($description, 3);
        
        $questions = [
            "Qu'est-ce que le cours '{$titre}' ?",
            "Quels sont les concepts clés abordés dans ce cours ?",
            "Comment appliquer les notions vues dans le cours ?"
        ];
        
        // Ajouter des questions basées sur les mots-clés
        foreach ($motsCles as $mot) {
            $questions[] = "Pouvez-vous expliquer le concept de '{$mot}' ?";
        }
        
        return array_slice($questions, 0, 5);
    }

    /**
     * Générer une fiche de révision HTML améliorée
     */
    public function genererFicheRevision(Cours $cours): string
    {
        $resume = $this->genererResume($cours);
        $nbQuestions = count($resume['questionsReponses']);
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Fiche révision - {$cours->getTitre()}</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: 'Segoe UI', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background: #f4f7f9;
                    padding: 20px;
                }
                .container {
                    max-width: 1000px;
                    margin: 0 auto;
                }
                .fiche-revision {
                    background: white;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                    padding: 30px;
                }
                h1 {
                    color: #007bff;
                    border-bottom: 3px solid #007bff;
                    padding-bottom: 15px;
                    margin-bottom: 25px;
                    font-size: 2.2em;
                }
                h2 {
                    color: #2c3e50;
                    margin: 30px 0 20px 0;
                    padding-left: 10px;
                    border-left: 5px solid #007bff;
                }
                h3 {
                    color: #34495e;
                    margin: 20px 0 10px 0;
                }
                .info-cours {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 20px;
                    border-radius: 10px;
                    margin: 20px 0;
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                }
                .info-item {
                    text-align: center;
                }
                .info-item strong {
                    display: block;
                    font-size: 0.9em;
                    opacity: 0.9;
                    margin-bottom: 5px;
                }
                .info-item span {
                    font-size: 1.2em;
                    font-weight: bold;
                }
                .resume-texte {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 10px;
                    border-left: 4px solid #28a745;
                    font-size: 1.1em;
                    margin: 20px 0;
                }
                .mots-cles {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    margin: 15px 0;
                }
                .mot-cle {
                    background: #e1f5fe;
                    color: #0288d1;
                    padding: 8px 15px;
                    border-radius: 25px;
                    font-size: 0.9em;
                    font-weight: 500;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                .ressources-liste {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                    gap: 15px;
                    margin: 20px 0;
                }
                .ressource-card {
                    background: white;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 15px;
                    transition: transform 0.2s, box-shadow 0.2s;
                }
                .ressource-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                }
                .ressource-type {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 3px;
                    font-size: 0.75em;
                    font-weight: bold;
                    text-transform: uppercase;
                    margin-bottom: 8px;
                }
                .type-pdf { background: #ff5252; color: white; }
                .type-video { background: #2196f3; color: white; }
                .type-lien { background: #4caf50; color: white; }
                .type-document { background: #ff9800; color: white; }
                .type-image { background: #9c27b0; color: white; }
                .ressource-titre {
                    font-weight: 600;
                    margin-bottom: 8px;
                }
                .ressource-desc {
                    font-size: 0.9em;
                    color: #6c757d;
                    margin-bottom: 10px;
                }
                .ressource-lien {
                    display: inline-block;
                    margin-top: 10px;
                    padding: 5px 10px;
                    background: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-size: 0.85em;
                }
                .ressource-lien:hover {
                    background: #0056b3;
                }
                .questions-container {
                    margin: 30px 0;
                }
                .question-item {
                    background: white;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    margin-bottom: 15px;
                    overflow: hidden;
                }
                .question-header {
                    background: #f8f9fa;
                    padding: 15px;
                    cursor: pointer;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .question-header:hover {
                    background: #e9ecef;
                }
                .question-header i {
                    color: #007bff;
                    transition: transform 0.3s;
                }
                .question-header.active i {
                    transform: rotate(90deg);
                }
                .reponse-content {
                    padding: 20px;
                    background: white;
                    border-top: 1px solid #e0e0e0;
                    display: none;
                    white-space: pre-line;
                }
                .reponse-content.show {
                    display: block;
                }
                .badge-categorie {
                    background: #6c757d;
                    color: white;
                    padding: 2px 8px;
                    border-radius: 12px;
                    font-size: 0.7em;
                    margin-left: 10px;
                }
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    background: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 5px;
                    border: none;
                    cursor: pointer;
                    font-size: 14px;
                }
                .btn-success {
                    background: #28a745;
                }
                .btn-print {
                    background: #6c757d;
                }
                .actions {
                    text-align: center;
                    margin: 30px 0;
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 10px;
                }
                @media print {
                    .btn, .actions, .question-header i {
                        display: none;
                    }
                    .reponse-content {
                        display: block !important;
                    }
                    .fiche-revision {
                        box-shadow: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='fiche-revision'>
                    <h1>📚 {$cours->getTitre()}</h1>
                    
                    <div class='info-cours'>
                        <div class='info-item'>
                            <strong>Matière</strong>
                            <span>{$cours->getMatiere()}</span>
                        </div>
                        <div class='info-item'>
                            <strong>Niveau</strong>
                            <span>{$cours->getNiveau()}</span>
                        </div>
                        <div class='info-item'>
                            <strong>Durée</strong>
                            <span>{$cours->getDuree()} minutes</span>
                        </div>
                        <div class='info-item'>
                            <strong>Ressources</strong>
                            <span>{$resume['statistiques']['totalRessources']}</span>
                        </div>
                    </div>
                    
                    <h2>📖 Résumé du cours</h2>
                    <div class='resume-texte'>
                        {$resume['court']}
                    </div>
                    
                    <h2>🔑 Mots-clés importants</h2>
                    <div class='mots-cles'>";
        
        foreach ($resume['motsCles'] as $mot) {
            $html .= "<span class='mot-cle'>#{$mot}</span>";
        }
        
        $html .= "
                    </div>
                    
                    <h2>📎 Ressources disponibles</h2>
                    <div class='ressources-liste'>";
        
        foreach ($resume['ressourcesAvecLiens'] as $ressource) {
            $typeClass = strtolower($ressource['type']);
            $html .= "
                        <div class='ressource-card'>
                            <span class='ressource-type type-{$typeClass}'>" . strtoupper($ressource['type']) . "</span>
                            <div class='ressource-titre'>{$ressource['titre']}</div>
                            <div class='ressource-desc'>{$ressource['description']}</div>";
            
            if ($ressource['lien'] && $ressource['lien'] != '#') {
                $html .= "<a href='{$ressource['lien']}' target='_blank' class='ressource-lien'>";
                if ($ressource['typeLien'] == 'fichier') {
                    $html .= "<i class='fas fa-download'></i> Télécharger";
                } else {
                    $html .= "<i class='fas fa-external-link-alt'></i> Voir la ressource";
                }
                $html .= "</a>";
            } else {
                $html .= "<span class='ressource-lien' style='background:#6c757d;'>Non disponible</span>";
            }
            
            $html .= "</div>";
        }
        
        $html .= "
                    </div>
                    
                    <h2>❓ Questions de révision</h2>
                    <div class='questions-container' id='questions'>";
        
        foreach ($resume['questionsReponses'] as $index => $qr) {
            $html .= "
                        <div class='question-item'>
                            <div class='question-header' onclick='toggleReponse({$index})'>
                                <i class='fas fa-chevron-right' id='icon-{$index}'></i>
                                {$qr['question']}
                                <span class='badge-categorie'>{$qr['categorie']}</span>
                            </div>
                            <div class='reponse-content' id='reponse-{$index}'>
                                {$qr['reponse']}
                            </div>
                        </div>";
        }
        
        $html .= "
                    </div>
                    
                    <div class='actions'>
                        <button class='btn btn-success' onclick='window.print()'>
                            🖨️ Imprimer la fiche
                        </button>
                        <button class='btn' onclick='revealAllAnswers()'>
                            🔍 Révéler toutes les réponses
                        </button>
                        <button class='btn btn-print' onclick='hideAllAnswers()'>
                            🙈 Cacher toutes les réponses
                        </button>
                    </div>
                </div>
            </div>
            
            <script>
                function toggleReponse(index) {
                    const reponse = document.getElementById('reponse-' + index);
                    const icon = document.getElementById('icon-' + index);
                    
                    if (reponse.classList.contains('show')) {
                        reponse.classList.remove('show');
                        icon.classList.remove('active');
                    } else {
                        reponse.classList.add('show');
                        icon.classList.add('active');
                    }
                }
                
                function revealAllAnswers() {
                    const nbQuestions = {$nbQuestions};
                    for(let i = 0; i < nbQuestions; i++) {
                        const reponse = document.getElementById('reponse-' + i);
                        const icon = document.getElementById('icon-' + i);
                        if (reponse) {
                            reponse.classList.add('show');
                            icon.classList.add('active');
                        }
                    }
                }
                
                function hideAllAnswers() {
                    const nbQuestions = {$nbQuestions};
                    for(let i = 0; i < nbQuestions; i++) {
                        const reponse = document.getElementById('reponse-' + i);
                        const icon = document.getElementById('icon-' + i);
                        if (reponse) {
                            reponse.classList.remove('show');
                            icon.classList.remove('active');
                        }
                    }
                }
            </script>
            
            <!-- Font Awesome pour les icônes -->
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
        </body>
        </html>";
        
        return $html;
    }
}