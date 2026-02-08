<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
<<<<<<< HEAD
use Symfony\Component\Validator\Constraints as Assert;
=======
<<<<<<< HEAD
use Symfony\Component\Validator\Constraints as Assert;
=======
<<<<<<< HEAD
<<<<<<< HEAD
use Symfony\Component\Validator\Constraints as Assert;
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
use Symfony\Component\Validator\Constraints as Assert;
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
    #[Assert\NotBlank(message: "Le titre est requis")]
    #[Assert\Length(
        min: 5,
        max: 150,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est requise")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères"
    )]
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column]
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
    #[Assert\NotBlank(message: "La durée est requise")]
    #[Assert\Positive(message: "La durée doit être positive")]
    #[Assert\Range(
        min: 1,
        max: 120,
        notInRangeMessage: "La durée doit être entre {{ min }} et {{ max }} minutes"
    )]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    #[Assert\NotBlank(message: "La note maximale est requise")]
    #[Assert\Positive(message: "La note doit être positive")]
    #[Assert\Range(
        min: 1,
        max: 100,
        notInRangeMessage: "La note doit être entre {{ min }} et {{ max }} points"
    )]
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
    private ?int $duree = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
    private ?string $noteMax = null;

    #[ORM\ManyToOne(inversedBy: 'question')]
    private ?User $user = null;

<<<<<<< HEAD
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Choix::class, cascade: ['remove'], orphanRemoval: true)]
=======
    /**
     * @var Collection<int, Choix>
     */
<<<<<<< HEAD
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Choix::class, cascade: ['remove'], orphanRemoval: true)]
=======
<<<<<<< HEAD
<<<<<<< HEAD
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Choix::class, cascade: ['remove'], orphanRemoval: true)]
=======
    #[ORM\OneToMany(targetEntity: Choix::class, mappedBy: 'question')]
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Choix::class, cascade: ['remove'], orphanRemoval: true)]
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
    private Collection $choix;

    public function __construct()
    {
        $this->choix = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

<<<<<<< HEAD
    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
=======
<<<<<<< HEAD
    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
=======
<<<<<<< HEAD
<<<<<<< HEAD
      public function setDateCreation(\DateTime $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
=======
    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
      public function setDateCreation(\DateTime $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;
        return $this;
    }

    public function getNoteMax(): ?string
    {
        return $this->noteMax;
    }

    public function setNoteMax(string $noteMax): static
    {
        $this->noteMax = $noteMax;
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
     * @return Collection<int, Choix>
     */
    public function getChoix(): Collection
    {
        return $this->choix;
    }

    public function addChoix(Choix $choix): static
    {
        if (!$this->choix->contains($choix)) {
            $this->choix->add($choix);
            $choix->setQuestion($this);
        }
        return $this;
    }

    public function removeChoix(Choix $choix): static
    {
        if ($this->choix->removeElement($choix)) {
            if ($choix->getQuestion() === $this) {
                $choix->setQuestion(null);
            }
        }
        return $this;
    }
}