<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
<<<<<<< HEAD
<<<<<<< HEAD
use Symfony\Component\Validator\Constraints as Assert;
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
use Symfony\Component\Validator\Constraints as Assert;
>>>>>>> gestionquiz

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
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
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column]
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
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
    private ?int $duree = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
    private ?string $noteMax = null;

    #[ORM\ManyToOne(inversedBy: 'question')]
    private ?User $user = null;

    /**
     * @var Collection<int, Choix>
     */
<<<<<<< HEAD
<<<<<<< HEAD
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Choix::class, cascade: ['remove'], orphanRemoval: true)]
=======
    #[ORM\OneToMany(targetEntity: Choix::class, mappedBy: 'question')]
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Choix::class, cascade: ['remove'], orphanRemoval: true)]
>>>>>>> gestionquiz
    private Collection $choix;

    public function __construct()
    {
        $this->choix = new ArrayCollection();
    }

<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======

>>>>>>> gestionquiz
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

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

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
            // set the owning side to null (unless already changed)
            if ($choix->getQuestion() === $this) {
                $choix->setQuestion(null);
            }
        }

        return $this;
    }
}
