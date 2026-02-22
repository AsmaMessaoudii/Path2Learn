<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le titre du cours est obligatoire")]
    #[Assert\Length(min: 5, max: 100,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description du cours est obligatoire")]
    #[Assert\Length(min: 20, minMessage: "Veuillez saisir une description plus détaillée (au moins {{ limit }} caractères)")]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le niveau d'étude est obligatoire")]
    #[Assert\Length(min: 2, max: 50,
        minMessage: "Le niveau doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le niveau ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $niveau = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "La matière est obligatoire")]
    #[Assert\Length(min: 3, max: 50,
        minMessage: "La matière doit contenir au moins {{ limit }} caractères",
        maxMessage: "La matière ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $matiere = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La durée du cours est obligatoire")]
    #[Assert\Positive(message: "La durée doit être un nombre positif")]
    #[Assert\LessThanOrEqual(value: 600, message: "La durée maximale d'un cours est de 10 heures (600 minutes)")]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de création est obligatoire")]
    #[Assert\LessThanOrEqual(value: "today", message: "La date de création ne peut pas être dans le futur")]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'email du professeur est obligatoire")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide")]
    #[Assert\Length(max: 100, maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères")]
    private ?string $emailProf = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le statut du cours est obligatoire")]
    #[Assert\Choice(
        choices: ['brouillon', 'publié', 'archivé'],
        message: "Le statut doit être : 'brouillon', 'publié' ou 'archivé'"
    )]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    private ?User $user = null;

    // ✅ Relation RessourcePedagogique
    #[ORM\OneToMany(
        targetEntity: RessourcePedagogique::class,
        mappedBy: 'cours',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[Assert\Valid]
    private Collection $ressourcePedagogiques;

    // ✅ Relation Evaluation
    #[ORM\OneToMany(
        mappedBy: 'cours',
        targetEntity: Evaluation::class,
        cascade: ['remove'],
        orphanRemoval: true
    )]
    private Collection $evaluations;

    // ✅ Relation LikeDislike
    #[ORM\OneToMany(
        mappedBy: 'cours',
        targetEntity: LikeDislike::class,
        cascade: ['remove'],
        orphanRemoval: true
    )]
    private Collection $likeDislikes;

    // ✅ Relation CourseSummary
    #[ORM\OneToMany(
        mappedBy: 'cours',
        targetEntity: CourseSummary::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $courseSummaries;

    public function __construct()
    {
        $this->ressourcePedagogiques = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        $this->likeDislikes = new ArrayCollection();
        $this->courseSummaries = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(string $niveau): static { $this->niveau = $niveau; return $this; }

    public function getMatiere(): ?string { return $this->matiere; }
    public function setMatiere(string $matiere): static { $this->matiere = $matiere; return $this; }

    public function getDuree(): ?int { return $this->duree; }
    public function setDuree(int $duree): static { $this->duree = $duree; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(\DateTimeInterface $dateCreation): static { $this->dateCreation = $dateCreation; return $this; }

    public function getEmailProf(): ?string { return $this->emailProf; }
    public function setEmailProf(string $emailProf): static { $this->emailProf = $emailProf; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    // --- RessourcePedagogique ---
    public function getRessourcePedagogiques(): Collection { return $this->ressourcePedagogiques; }

    public function addRessourcePedagogique(RessourcePedagogique $r): static
    {
        if (!$this->ressourcePedagogiques->contains($r)) {
            $this->ressourcePedagogiques->add($r);
            $r->setCours($this);
        }
        return $this;
    }

    public function removeRessourcePedagogique(RessourcePedagogique $r): static
    {
        if ($this->ressourcePedagogiques->removeElement($r)) {
            if ($r->getCours() === $this) $r->setCours(null);
        }
        return $this;
    }

    // --- Evaluations ---
    public function getEvaluations(): Collection { return $this->evaluations; }

    // --- LikeDislikes ---
    public function getLikeDislikes(): Collection { return $this->likeDislikes; }

    // --- CourseSummaries ---
    public function getCourseSummaries(): Collection { return $this->courseSummaries; }

    public function addCourseSummary(CourseSummary $s): static
    {
        if (!$this->courseSummaries->contains($s)) {
            $this->courseSummaries->add($s);
            $s->setCours($this);
        }
        return $this;
    }

    public function removeCourseSummary(CourseSummary $s): static
    {
        if ($this->courseSummaries->removeElement($s)) {
            if ($s->getCours() === $this) $s->setCours(null);
        }
        return $this;
    }

    public function getLatestSummary(): ?CourseSummary
    {
        if ($this->courseSummaries->isEmpty()) return null;
        $iterator = $this->courseSummaries->getIterator();
        $iterator->uasort(fn($a, $b) => $b->getGeneratedAt() <=> $a->getGeneratedAt());
        return $iterator->current();
    }

    public function __toString(): string { return $this->titre ?? 'Nouveau cours'; }
}