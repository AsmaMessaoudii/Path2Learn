<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 150)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    /**
     * @var Collection<int, Cours>
     */
    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'user')]
    private Collection $cours;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'user')]
    private Collection $question;

    /**
     * @var Collection<int, Portfolio>
     */
    #[ORM\OneToMany(targetEntity: Portfolio::class, mappedBy: 'user')]
    private Collection $portfolio;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'user')]
    private Collection $evenement;

    /**
     * @var Collection<int, ParticipationEvent>
     */
    #[ORM\OneToMany(targetEntity: ParticipationEvent::class, mappedBy: 'user')]
    private Collection $participationEvent;

    public function __construct()
    {
        $this->cours = new ArrayCollection();
        $this->question = new ArrayCollection();
        $this->portfolio = new ArrayCollection();
        $this->evenement = new ArrayCollection();
        $this->participationEvent = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Nom d'affichage pour les templates (prenom + nom, ou email).
     */
    public function getUsername(): string
    {
        $name = trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
        return $name !== '' ? $name : ($this->email ?? '');
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

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

    /**
     * @return Collection<int, Cours>
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCour(Cours $cour): static
    {
        if (!$this->cours->contains($cour)) {
            $this->cours->add($cour);
            $cour->setUser($this);
        }

        return $this;
    }

    public function removeCour(Cours $cour): static
    {
        if ($this->cours->removeElement($cour)) {
            // set the owning side to null (unless already changed)
            if ($cour->getUser() === $this) {
                $cour->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestion(): Collection
    {
        return $this->question;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->question->contains($question)) {
            $this->question->add($question);
            $question->setUser($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->question->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getUser() === $this) {
                $question->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Portfolio>
     */
    public function getPortfolio(): Collection
    {
        return $this->portfolio;
    }

    public function addPortfolio(Portfolio $portfolio): static
    {
        if (!$this->portfolio->contains($portfolio)) {
            $this->portfolio->add($portfolio);
            $portfolio->setUser($this);
        }

        return $this;
    }

    public function removePortfolio(Portfolio $portfolio): static
    {
        if ($this->portfolio->removeElement($portfolio)) {
            // set the owning side to null (unless already changed)
            if ($portfolio->getUser() === $this) {
                $portfolio->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenement(): Collection
    {
        return $this->evenement;
    }

    public function addEvenement(Evenement $evenement): static
    {
        if (!$this->evenement->contains($evenement)) {
            $this->evenement->add($evenement);
            $evenement->setUser($this);
        }

        return $this;
    }

    public function removeEvenement(Evenement $evenement): static
    {
        if ($this->evenement->removeElement($evenement)) {
            // set the owning side to null (unless already changed)
            if ($evenement->getUser() === $this) {
                $evenement->setUser(null);
            }
        }

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
            $participationEvent->setUser($this);
        }

        return $this;
    }

    public function removeParticipationEvent(ParticipationEvent $participationEvent): static
    {
        if ($this->participationEvent->removeElement($participationEvent)) {
            // set the owning side to null (unless already changed)
            if ($participationEvent->getUser() === $this) {
                $participationEvent->setUser(null);
            }
        }

        return $this;
    }
<<<<<<< HEAD

=======
<<<<<<< HEAD
    public function getUsername(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
=======
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
      public function getUsername(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

<<<<<<< HEAD
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
}
