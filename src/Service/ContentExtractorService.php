<?php

namespace App\Service;

use App\Entity\RessourcePedagogique;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

class ContentExtractorService
{
    private string $uploadDirectory;
    
    public function __construct(string $uploadDirectory)
    {
        $this->uploadDirectory = $uploadDirectory;
    }
    
    /**
     * Extrait le contenu texte de toutes les ressources
     */
    public function extractContentFromResources(array $ressources): array
    {
        $contents = [];
        
        foreach ($ressources as $ressource) {
            if ($ressource->getFileName()) {
                $filePath = $this->uploadDirectory . '/' . $ressource->getFileName();
                $content = $this->extractFileContent($filePath, $ressource);
                
                if ($content) {
                    $contents[] = [
                        'titre' => $ressource->getTitre() ?? $ressource->getFileName(),
                        'type' => $this->getFileType($ressource->getFileName()),
                        'contenu' => $content,
                        'importance' => $this->calculateImportance($ressource)
                    ];
                }
            }
        }
        
        return $contents;
    }
    
    /**
     * Extrait le contenu selon le type de fichier
     */
    private function extractFileContent(string $filePath, RessourcePedagogique $ressource): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }
        
        $extension = strtolower(pathinfo($ressource->getFileName(), PATHINFO_EXTENSION));
        
        try {
            switch ($extension) {
                case 'pdf':
                    return $this->extractPdfContent($filePath);
                    
                case 'txt':
                case 'md':
                case 'csv':
                    return file_get_contents($filePath);
                    
                case 'docx':
                case 'doc':
                    return $this->extractWordContent($filePath);
                    
                case 'pptx':
                case 'ppt':
                    return $this->extractPowerPointContent($filePath);
                    
                default:
                    // Pour les autres types, on utilise le titre et description
                    return $ressource->getDescription() ?? '';
            }
        } catch (\Exception $e) {
            return $ressource->getDescription() ?? '';
        }
    }
    
    /**
     * Extrait le texte d'un PDF
     */
    private function extractPdfContent(string $filePath): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        return $pdf->getText();
    }
    
    /**
     * Extrait le texte d'un document Word
     */
    private function extractWordContent(string $filePath): string
    {
        $phpWord = WordIOFactory::load($filePath);
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
    
    /**
     * Extrait le texte d'une présentation PowerPoint
     */
    private function extractPowerPointContent(string $filePath): string
    {
        // Version simplifiée - à améliorer avec une vraie bibliothèque PPTX
        return "Contenu PowerPoint: " . basename($filePath);
    }
    
    /**
     * Détermine le type de fichier
     */
    private function getFileType(string $fileName): string
    {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        return match($ext) {
            'pdf' => 'PDF',
            'docx', 'doc' => 'Document Word',
            'pptx', 'ppt' => 'Présentation',
            'txt', 'md' => 'Texte',
            'csv', 'xlsx', 'xls' => 'Tableur',
            'jpg', 'jpeg', 'png', 'gif' => 'Image',
            'mp4', 'mov', 'avi' => 'Vidéo',
            'mp3', 'wav' => 'Audio',
            default => 'Fichier'
        };
    }
    
    /**
     * Calcule l'importance relative d'une ressource
     */
    private function calculateImportance(RessourcePedagogique $ressource): float
    {
        $score = 1.0;
        
        // Les PDF sont souvent plus importants
        if ($ressource->getFileName() && str_contains(strtolower($ressource->getFileName()), '.pdf')) {
            $score += 0.5;
        }
        
        // Les ressources avec description sont plus importantes
        if ($ressource->getDescription()) {
            $score += 0.3;
        }
        
        // Les plus récentes sont plus importantes
        if ($ressource->getDateAjout() && $ressource->getDateAjout() > new \DateTime('-30 days')) {
            $score += 0.2;
        }
        
        return min($score, 2.0);
    }
}