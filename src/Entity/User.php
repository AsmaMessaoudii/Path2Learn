<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

    #[ORM\Column(type: 'string', enumType: UserRole::class)]
    private ?UserRole $role = null;

    #[ORM\Column(type: 'string', enumType: UserStatus::class)]
    private ?UserStatus $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    /**
     * @var Collection<int, Cours>
     */
    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $cours;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'user')]
    private Collection $questions;

    /**
     * @var Collection<int, Portfolio>
     */
    #[ORM\OneToMany(targetEntity: Portfolio::class, mappedBy: 'user')]
    private Collection $portfolios;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'user')]
    private Collection $evenements;

    /**
     * @var Collection<int, ParticipationEvent>
     */
    #[ORM\OneToMany(targetEntity: ParticipationEvent::class, mappedBy: 'user')]
    private Collection $participationEvents;

    public function __construct()
    {
        $this->cours = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->portfolios = new ArrayCollection();
        $this->evenements = new ArrayCollection();
        $this->participationEvents = new ArrayCollection();
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

    public function getRole(): ?UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getStatus(): ?UserStatus
    {
        return $this->status;
    }

    public function setStatus(UserStatus $status): static
    {
        $this->status = $status;
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

    // ========== UserInterface Implementation ==========

    /**
     * Returns the roles granted to the user.
     */
    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        // Add role based on enum value
        if ($this->role === UserRole::ADMIN) {
            $roles[] = 'ROLE_ADMIN';
        } elseif ($this->role === UserRole::TEACHER) {
            $roles[] = 'ROLE_TEACHER';
        } elseif ($this->role === UserRole::STUDENT) {
            $roles[] = 'ROLE_STUDENT';
        }

        return array_unique($roles);
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Returns the identifier for this user (e.g. username or email address).
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Returns the password used to authenticate the user.
     */
    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    // ========== Collections Methods ==========

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
            if ($cour->getUser() === $this) {
                $cour->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setUser($this);
        }
        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getUser() === $this) {
                $question->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Portfolio>
     */
    public function getPortfolios(): Collection
    {
        return $this->portfolios;
    }

    public function addPortfolio(Portfolio $portfolio): static
    {
        if (!$this->portfolios->contains($portfolio)) {
            $this->portfolios->add($portfolio);
            $portfolio->setUser($this);
        }
        return $this;
    }

    public function removePortfolio(Portfolio $portfolio): static
    {
        if ($this->portfolios->removeElement($portfolio)) {
            if ($portfolio->getUser() === $this) {
                $portfolio->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): static
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
            $evenement->setUser($this);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): static
    {
        if ($this->evenements->removeElement($evenement)) {
            if ($evenement->getUser() === $this) {
                $evenement->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ParticipationEvent>
     */
    public function getParticipationEvents(): Collection
    {
        return $this->participationEvents;
    }

    public function addParticipationEvent(ParticipationEvent $participationEvent): static
    {
        if (!$this->participationEvents->contains($participationEvent)) {
            $this->participationEvents->add($participationEvent);
            $participationEvent->setUser($this);
        }
        return $this;
    }

    public function removeParticipationEvent(ParticipationEvent $participationEvent): static
    {
        if ($this->participationEvents->removeElement($participationEvent)) {
            if ($participationEvent->getUser() === $this) {
                $participationEvent->setUser(null);
            }
        }
        return $this;
    }
}