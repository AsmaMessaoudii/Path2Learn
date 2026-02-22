<?php

namespace App\Entity;

use App\Repository\BadRatingReasonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BadRatingReasonRepository::class)]
#[ORM\Table(name: 'bad_rating_reason')]
class BadRatingReason
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $reason = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $customReason = null;

    #[ORM\ManyToOne(targetEntity: Evaluation::class)]
    #[ORM\JoinColumn(name: 'evaluation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Evaluation $evaluation = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getCustomReason(): ?string
    {
        return $this->customReason;
    }

    public function setCustomReason(?string $customReason): self
    {
        $this->customReason = $customReason;
        return $this;
    }

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $evaluation): self
    {
        $this->evaluation = $evaluation;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Obtenir le label lisible de la raison
     */
    public function getReasonLabel(): string
    {
        $labels = [
            'contenu_difficile' => 'Contenu trop difficile',
            'contenu_simple' => 'Contenu trop simple',
            'qualite_mediocre' => 'Qualité du cours médiocre',
            'problemes_techniques' => 'Problèmes techniques',
            'formateur_peu_clair' => 'Formateur peu clair',
            'manque_exercices' => 'Manque d\'exercices pratiques',
            'ressources_insuffisantes' => 'Ressources insuffisantes',
            'autre' => 'Autre'
        ];

        return $labels[$this->reason] ?? $this->reason;
    }

    /**
     * Obtenir la liste des raisons disponibles
     */
    public static function getAvailableReasons(): array
    {
        return [
            'contenu_difficile' => 'Contenu trop difficile',
            'contenu_simple' => 'Contenu trop simple',
            'qualite_mediocre' => 'Qualité du cours médiocre',
            'problemes_techniques' => 'Problèmes techniques',
            'formateur_peu_clair' => 'Formateur peu clair',
            'manque_exercices' => 'Manque d\'exercices pratiques',
            'ressources_insuffisantes' => 'Ressources insuffisantes',
            'autre' => 'Autre'
        ];
    }
}