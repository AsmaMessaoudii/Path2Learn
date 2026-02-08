<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
<<<<<<< HEAD
<<<<<<< HEAD
=======
use Symfony\Component\Validator\Constraints as Assert;
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $niveau = null;

    #[ORM\Column(length: 50)]
    private ?string $matiere = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(length: 100)]
    private ?string $emailProf = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
<<<<<<< HEAD
=======
    #[Assert\NotBlank(message: "Le titre du cours est obligatoire")]
    #[Assert\Length(
        min: 5,
        max: 100,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description du cours est obligatoire")]
    #[Assert\Length(
        min: 20,
        minMessage: "Veuillez saisir une description plus détaillée (au moins {{ limit }} caractères)"
    )]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le niveau d'étude est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le niveau doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le niveau ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $niveau = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "La matière est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "La matière doit contenir au moins {{ limit }} caractères",
        maxMessage: "La matière ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $matiere = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La durée du cours est obligatoire")]
    #[Assert\Positive(message: "La durée doit être un nombre positif")]
    #[Assert\LessThanOrEqual(
        value: 600,
        message: "La durée maximale d'un cours est de 10 heures (600 minutes)"
    )]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de création est obligatoire")]
    #[Assert\LessThanOrEqual(
        value: "today",
        message: "La date de création ne peut pas être dans le futur"
    )]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'email du professeur est obligatoire")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide")]
    #[Assert\Length(
        max: 100,
        maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $emailProf = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le statut du cours est obligatoire")]
    #[Assert\Choice(
        choices: ['brouillon', 'publié', 'archivé'],
        message: "Le statut doit être : 'brouillon', 'publié' ou 'archivé'"
    )]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
   // #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
    private ?User $user = null;

    /**
     * @var Collection<int, RessourcePedagogique>
     */
<<<<<<< HEAD
<<<<<<< HEAD
    #[ORM\OneToMany(targetEntity: RessourcePedagogique::class, mappedBy: 'cours')]
=======
    #[ORM\OneToMany(
        targetEntity: RessourcePedagogique::class,
        mappedBy: 'cours',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[Assert\Valid]
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
    #[ORM\OneToMany(targetEntity: RessourcePedagogique::class, mappedBy: 'cours')]
>>>>>>> gestionquiz
    private Collection $ressourcePedagogiques;

    public function __construct()
    {
        $this->ressourcePedagogiques = new ArrayCollection();
<<<<<<< HEAD
<<<<<<< HEAD
=======
        $this->dateCreation = new \DateTime();
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
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
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    public function getMatiere(): ?string
    {
        return $this->matiere;
    }

    public function setMatiere(string $matiere): static
    {
        $this->matiere = $matiere;
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    public function getEmailProf(): ?string
    {
        return $this->emailProf;
    }

    public function setEmailProf(string $emailProf): static
    {
        $this->emailProf = $emailProf;
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    /**
     * @return Collection<int, RessourcePedagogique>
     */
    public function getRessourcePedagogiques(): Collection
    {
        return $this->ressourcePedagogiques;
    }

    public function addRessourcePedagogique(RessourcePedagogique $ressourcePedagogique): static
    {
        if (!$this->ressourcePedagogiques->contains($ressourcePedagogique)) {
            $this->ressourcePedagogiques->add($ressourcePedagogique);
            $ressourcePedagogique->setCours($this);
        }
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
        return $this;
    }

    public function removeRessourcePedagogique(RessourcePedagogique $ressourcePedagogique): static
    {
        if ($this->ressourcePedagogiques->removeElement($ressourcePedagogique)) {
<<<<<<< HEAD
<<<<<<< HEAD
            // set the owning side to null (unless already changed)
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
            // set the owning side to null (unless already changed)
>>>>>>> gestionquiz
            if ($ressourcePedagogique->getCours() === $this) {
                $ressourcePedagogique->setCours(null);
            }
        }
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz

        return $this;
    }
}
<<<<<<< HEAD
=======
        return $this;
    }
    public function __toString(): string
    {
        return $this->titre ?? 'Nouveau cours';
    }
}
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
