<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne doit pas être vide")]
    #[Assert\Length(min: 5, max: 255, minMessage: "Le titre doit faire au moins 5 caractères", maxMessage: "Le titre ne peut pas dépasser 255 caractères")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description ne doit pas être vide")]
    #[Assert\Length(min: 10, minMessage: "La description doit faire au moins 10 caractères")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de début est obligatoire")]
    #[Assert\GreaterThan("today", message: "La date de début doit être dans le futur")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire")]
    #[Assert\GreaterThan(propertyPath: "dateDebut", message: "La date de fin doit être après la date de début")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le lieu est obligatoire")]
    #[Assert\Length(min: 3, minMessage: "Le lieu doit contenir au moins 3 caractères")]
    private ?string $lieu = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La capacité maximale est obligatoire")]
    #[Assert\Positive(message: "La capacité doit être un nombre positif")]
    #[Assert\Range(min: 1, max: 10000, notInRangeMessage: "La capacité doit être entre 1 et 10000")]
    private ?int $capaciteMax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(choices: ['ouvert', 'complet', 'annulé', 'terminé'], message: "Le statut doit être valide")]
    private ?string $statut = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\ManyToOne(inversedBy: 'evenement')]
    private ?User $user = null;

    /**
     * @var Collection<int, ParticipationEvent>
     */
    #[ORM\OneToMany(targetEntity: ParticipationEvent::class, mappedBy: 'evenement', cascade: ['persist', 'remove'])]
    private Collection $participationEvent;

    public function __construct()
    {
        $this->participationEvent = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->statut = 'ouvert';
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getCapaciteMax(): ?int
    {
        return $this->capaciteMax;
    }

    public function setCapaciteMax(int $capaciteMax): static
    {
        $this->capaciteMax = $capaciteMax;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, ParticipationEvent>
     */
    public function getParticipationEvent(): Collection
    {
        return $this->participationEvent;
    }

    public function addParticipationEvent(ParticipationEvent $participationEvent): static
    {
        if (!$this->participationEvent->contains($participationEvent)) {
            $this->participationEvent->add($participationEvent);
            $participationEvent->setEvenement($this);
        }

        return $this;
    }

    public function removeParticipationEvent(ParticipationEvent $participationEvent): static
    {
        if ($this->participationEvent->removeElement($participationEvent)) {
            // set the owning side to null (unless already changed)
            if ($participationEvent->getEvenement() === $this) {
                $participationEvent->setEvenement(null);
            }
        }

        return $this;
    }

    // Méthodes métier
    public function getPlacesDisponibles(): int
    {
        return $this->capaciteMax - $this->participationEvent->count();
    }

    public function isComplet(): bool
    {
        return $this->participationEvent->count() >= $this->capaciteMax;
    }

    public function getTauxRemplissage(): float
    {
        if ($this->capaciteMax === 0) {
            return 0;
        }
        return ($this->participationEvent->count() / $this->capaciteMax) * 100;
    }

    public function isPasse(): bool
    {
        return $this->dateFin < new \DateTime();
    }
}