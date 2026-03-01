<?php
// src/Service/ExtractionTexteService.php

namespace App\Service;

use App\Entity\RessourcePedagogique;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Symfony\Component\HttpKernel\KernelInterface;

class ExtractionTexteService
{
    private string $uploadDir;

    public function __construct(KernelInterface $kernel)
    {
        $this->uploadDir = $kernel->getProjectDir() . '/public/uploads/ressources/';
    }

    /**
     * Extraire le texte d'une ressource
     */
    public function extraireTexte(RessourcePedagogique $ressource): ?string
    {
        $type = $ressource->getType();
        $fileName = $ressource->getFileName();

        if (!$fileName) {
            return null;
        }

        $filePath = $this->uploadDir . $fileName;

        if (!file_exists($filePath)) {
            return null;
        }

        switch ($type) {
            case 'PDF':
                return $this->extraireTextePdf($filePath);
            case 'Document':
            case 'Présentation':
                return $this->extraireTexteWord($filePath);
            case 'Lien':
                return $this->extraireTexteLien($ressource->getUrl());
            default:
                return null;
        }
    }

    /**
     * Extraire texte d'un PDF
     */
    private function extraireTextePdf(string $filePath): ?string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            $texte = $pdf->getText();
            
            // Nettoyer le texte
            $texte = preg_replace('/\s+/', ' ', $texte);
            $texte = trim($texte);
            
            return $texte;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extraire texte d'un document Word
     */
    private function extraireTexteWord(string $filePath): ?string
    {
        try {
            $phpWord = WordIOFactory::load($filePath);
            $texte = '';
            
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $texte .= $element->getText() . ' ';
                    }
                }
            }
            
            return trim($texte);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extraire contenu d'un lien (si c'est une page web)
     */
    private function extraireTexteLien(?string $url): ?string
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        try {
            // Pour YouTube
            if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                return $this->extraireInfoYoutube($url);
            }

            // Pour les pages web simples
            $html = file_get_contents($url);
            if ($html) {
                // Supprimer les balises scripts et styles
                $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
                $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
                
                // Extraire le texte
                $texte = strip_tags($html);
                $texte = preg_replace('/\s+/', ' ', $texte);
                
                return substr($texte, 0, 5000); // Limiter à 5000 caractères
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Extraire info d'une vidéo YouTube
     */
    private function extraireInfoYoutube(string $url): ?string
    {
        // Version simple - extraire l'ID de la vidéo
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $videoId = $params['v'] ?? null;

        if ($videoId) {
            return "Vidéo YouTube ID: {$videoId}. Pour voir le résumé, regardez la description et les commentaires de la vidéo.";
        }

        return "Lien YouTube - regardez la vidéo pour plus d'informations.";
    }

    /**
     * Obtenir les métadonnées du fichier
     */
    public function getMetadonnees(RessourcePedagogique $ressource): array
    {
        $fileName = $ressource->getFileName();
        if (!$fileName) {
            return [];
        }

        $filePath = $this->uploadDir . $fileName;
        if (!file_exists($filePath)) {
            return [];
        }

        $metadonnees = [
            'taille' => filesize($filePath),
            'date_modification' => date('Y-m-d H:i:s', filemtime($filePath)),
            'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
        ];

        // Pour les PDF, obtenir le nombre de pages
        if ($ressource->getType() === 'PDF') {
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($filePath);
                $metadonnees['pages'] = count($pdf->getPages());
            } catch (\Exception $e) {
                $metadonnees['pages'] = null;
            }
        }

        return $metadonnees;
    }
}