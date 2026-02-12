<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
#[ORM\Table(name: 'projet')]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: "Le titre du projet est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 150,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9À-ÿ\s\-_,.!?()'\"&]+$/u",
        message: "Le titre contient des caractères non autorisés"
    )]
    private ?string $titreProjet = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le texte court est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "Le texte doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le texte ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $text = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 30,
        max: 2000,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Les technologies sont obligatoires")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Les technologies ne peuvent pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9À-ÿ\s\-,.()\/&+#]+$/u",
        message: "Les technologies contiennent des caractères non autorisés"
    )]
    private ?string $technologies = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de réalisation est obligatoire")]
    #[Assert\LessThanOrEqual(
        "today",
        message: "La date ne peut pas être dans le futur"
    )]
    private ?\DateTimeInterface $dateRealisation = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le lien de démonstration est obligatoire")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le lien ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Url(
        message: "Veuillez entrer une URL valide (ex: https://exemple.com)",
        protocols: ['http', 'https']
    )]
    private ?string $lienDemo = null;

    #[ORM\ManyToOne(targetEntity: Portfolio::class, inversedBy: 'projet')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Portfolio $portfolio = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreProjet(): ?string
    {
        return $this->titreProjet;
    }

    public function setTitreProjet(string $titreProjet): static
    {
        $this->titreProjet = trim($titreProjet);
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = trim($text);
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = trim($description);
        return $this;
    }

    public function getTechnologies(): ?string
    {
        return $this->technologies;
    }

    public function setTechnologies(string $technologies): static
    {
        $this->technologies = trim($technologies);
        return $this;
    }

    public function getDateRealisation(): ?\DateTimeInterface
    {
        return $this->dateRealisation;
    }

    public function setDateRealisation(?\DateTimeInterface $dateRealisation): static
{
    $this->dateRealisation = $dateRealisation;
    return $this;
}


    public function getLienDemo(): ?string
    {
        return $this->lienDemo;
    }

    public function setLienDemo(string $lienDemo): static
    {
        $this->lienDemo = trim($lienDemo);
        return $this;
    }

    public function getPortfolio(): ?Portfolio
    {
        return $this->portfolio;
    }

    public function setPortfolio(?Portfolio $portfolio): static
    {
        $this->portfolio = $portfolio;
        return $this;
    }

    public function __toString(): string
    {
        return $this->titreProjet ?? 'Nouveau Projet';
    }

    /**
     * Nettoyage des données avant enregistrement
     */
    public function sanitize(): void
    {
        $this->titreProjet = htmlspecialchars($this->titreProjet, ENT_QUOTES, 'UTF-8');
        $this->text = htmlspecialchars($this->text, ENT_QUOTES, 'UTF-8');
        $this->description = strip_tags($this->description, '<p><br><strong><em><ul><li><ol>');
        $this->technologies = htmlspecialchars($this->technologies, ENT_QUOTES, 'UTF-8');
        $this->lienDemo = filter_var($this->lienDemo, FILTER_SANITIZE_URL);
    }

    
}