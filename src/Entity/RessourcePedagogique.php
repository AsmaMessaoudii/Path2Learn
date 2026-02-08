<?php

namespace App\Entity;

use App\Repository\RessourcePedagogiqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: RessourcePedagogiqueRepository::class)]
#[Vich\Uploadable]
class RessourcePedagogique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le titre de la ressource est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le type de ressource est obligatoire")]
    #[Assert\Choice(
        choices: ['PDF', 'Vidéo', 'Lien', 'Document', 'Présentation', 'Audio', 'Image', 'Exercice'],
        message: "Le type doit être parmi : PDF, Vidéo, Lien, Document, Présentation, Audio, Image, Exercice"
    )]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "L'URL ne peut pas dépasser {{ limit }} caractères")]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileName = null;

    #[Vich\UploadableField(mapping: 'ressource_file', fileNameProperty: 'fileName')]
    #[Assert\File(
        maxSize: '10M',
        mimeTypes: [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'audio/mpeg',
            'audio/wav',
            'text/plain'
        ],
        mimeTypesMessage: 'Veuillez télécharger un fichier valide (PDF, Word, Excel, PowerPoint, Image, Vidéo, Audio, Texte)'
    )]
    private ?File $file = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date d'ajout est obligatoire")]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Cours::class, inversedBy: 'ressourcePedagogiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cours $cours = null;

    public function __construct()
    {
        $this->dateAjout = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null): static
    {
        $this->file = $file;
        
        if ($file) {
            $this->updatedAt = new \DateTime();
        }
        
        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): static
    {
        $this->dateAjout = $dateAjout;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;
        return $this;
    }

    public function getFileUrl(): ?string
    {
        if (!$this->fileName) {
            return null;
        }
        
        return '/uploads/ressources/' . $this->fileName;
    }

    public function getMimeType(): ?string
    {
        if ($this->file instanceof File) {
            return $this->file->getMimeType();
        }
        
        if ($this->fileName) {
            $extension = pathinfo($this->fileName, PATHINFO_EXTENSION);
            return $this->getMimeTypeFromExtension($extension);
        }
        
        return null;
    }

    private function getMimeTypeFromExtension(string $extension): string
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'txt' => 'text/plain',
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }

    public function isImage(): bool
    {
        $mimeType = $this->getMimeType();
        return $mimeType && str_starts_with($mimeType, 'image/');
    }

    public function isVideo(): bool
    {
        $mimeType = $this->getMimeType();
        return $mimeType && str_starts_with($mimeType, 'video/');
    }

    public function isPdf(): bool
    {
        return $this->getMimeType() === 'application/pdf';
    }

    public function getDisplayUrl(): string
    {
        if ($this->fileName) {
            return $this->getFileUrl();
        }
        
        return $this->url ?? '';
    }

    public function getDisplayType(): string
    {
        if ($this->fileName) {
            if ($this->isImage()) {
                return 'Image';
            } elseif ($this->isVideo()) {
                return 'Vidéo';
            } elseif ($this->isPdf()) {
                return 'PDF';
            } else {
                return 'Fichier';
            }
        }
        
        return $this->type ?? '';
    }

    public function getFileIcon(): string
    {
        if ($this->fileName) {
            $extension = strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));
            
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                return 'bi-file-earmark-image';
            }
            
            if ($extension === 'pdf') {
                return 'bi-file-earmark-pdf';
            }
            
            if (in_array($extension, ['mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'flv'])) {
                return 'bi-file-earmark-play';
            }
            
            if (in_array($extension, ['mp3', 'wav', 'ogg', 'm4a', 'flac'])) {
                return 'bi-file-earmark-music';
            }
            
            if (in_array($extension, ['doc', 'docx'])) {
                return 'bi-file-earmark-word';
            }
            
            if (in_array($extension, ['xls', 'xlsx', 'csv'])) {
                return 'bi-file-earmark-excel';
            }
            
            if (in_array($extension, ['ppt', 'pptx'])) {
                return 'bi-file-earmark-ppt';
            }
            
            if (in_array($extension, ['txt', 'rtf', 'md'])) {
                return 'bi-file-earmark-text';
            }
            
            if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                return 'bi-file-earmark-zip';
            }
        }
        
        return 'bi-file-earmark';
    }

    public function fileExists(string $uploadDirectory): bool
    {
        if (!$this->fileName) {
            return false;
        }
        
        $filePath = $uploadDirectory . '/' . $this->fileName;
        return file_exists($filePath);
    }

    public function __toString(): string
    {
        return $this->titre ?? 'Nouvelle ressource';
    }
}