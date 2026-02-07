<?php

namespace App\Entity;

use App\Repository\RessourcePedagogiqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RessourcePedagogiqueRepository::class)]
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

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'URL de la ressource est obligatoire")]
    #[Assert\Url(message: "L'URL '{{ value }}' n'est pas valide")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'URL ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $url = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date d'ajout est obligatoire")]
    #[Assert\LessThanOrEqual(
        value: "today",
        message: "La date d'ajout ne peut pas être dans le futur"
    )]
    private ?\DateTime $dateAjout = null;

    #[ORM\ManyToOne(inversedBy: 'ressourcePedagogiques')]
    #[Assert\NotNull(message: "Le cours associé est obligatoire")]
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

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getDateAjout(): ?\DateTime
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTime $dateAjout): static
    {
        $this->dateAjout = $dateAjout;
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

    public function __toString(): string
    {
        return $this->titre ?? 'Nouvelle ressource';
    }
}