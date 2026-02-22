<?php

namespace App\Entity;

use App\Repository\PortfolioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PortfolioRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'portfolio')]
class Portfolio
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
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
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 20,
        max: 2000,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateMiseAjour = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'portfolio')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    /**
     * @var Collection<int, Projet>
     */
    #[ORM\OneToMany(
        targetEntity: Projet::class,
        mappedBy: 'portfolio',
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private Collection $projet;

    public function __construct()
    {
        $this->projet = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->dateMiseAjour = new \DateTime();
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
        $this->titre = trim($titre);
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateMiseAjour(): ?\DateTimeInterface
    {
        return $this->dateMiseAjour;
    }

    public function setDateMiseAjour(\DateTimeInterface $dateMiseAjour): static
    {
        $this->dateMiseAjour = $dateMiseAjour;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreationDate(): void
    {
        if ($this->dateCreation === null) {
            $this->dateCreation = new \DateTime();
        }
        if ($this->dateMiseAjour === null) {
            $this->dateMiseAjour = new \DateTime();
        }
    }

    #[ORM\PreUpdate]
    public function setUpdateDate(): void
    {
        $this->dateMiseAjour = new \DateTime();
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
     * @return Collection<int, Projet>
     */
    public function getProjet(): Collection
    {
        return $this->projet;
    }

    public function addProjet(Projet $projet): static
    {
        if (!$this->projet->contains($projet)) {
            $this->projet->add($projet);
            $projet->setPortfolio($this);
        }
        return $this;
    }

    public function removeProjet(Projet $projet): static
    {
        if ($this->projet->removeElement($projet)) {
            if ($projet->getPortfolio() === $this) {
                $projet->setPortfolio(null);
            }
        }
        return $this;
    }

    // Méthode de nettoyage des données
    public function sanitize(): void
    {
        if ($this->titre) {
            $this->titre = htmlspecialchars(trim($this->titre), ENT_QUOTES, 'UTF-8');
        }
        if ($this->description) {
            $this->description = strip_tags(trim($this->description), '<p><br><strong><em><ul><li><ol>');
        }
    }

    public function __toString(): string
    {
        return $this->titre ?? 'Nouveau Portfolio';
    }
}