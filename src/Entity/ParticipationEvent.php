<?php

namespace App\Entity;

use App\Repository\ParticipationEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipationEventRepository::class)]
class ParticipationEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(min: 4, max: 100, minMessage: "Le nom doit faire au moins 4 caractères")]
    private ?string $nomParticipant = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    #[Assert\Length(min: 4, max: 100, minMessage: "Le prénom doit faire au moins 4 caractères")]
    private ?string $prenomParticipant = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email {{ value }} n'est pas valide")]
    private ?string $emailParticipant = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: "/^[0-9]{8,15}$/", message: "Le téléphone doit contenir entre 8 et 15 chiffres")]
    private ?string $telephoneParticipant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(choices: ['confirmé', 'en_attente', 'annulé', 'présent', 'absent'], message: "Statut invalide")]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'participationEvent')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evenement $evenement = null;

    #[ORM\ManyToOne(inversedBy: 'participationEvent')]
    private ?User $user = null;

    public function __construct()
    {
        $this->dateInscription = new \DateTime();
        $this->statut = 'confirmé';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomParticipant(): ?string
    {
        return $this->nomParticipant;
    }

    public function setNomParticipant(string $nomParticipant): static
    {
        $this->nomParticipant = $nomParticipant;

        return $this;
    }

    public function getPrenomParticipant(): ?string
    {
        return $this->prenomParticipant;
    }

    public function setPrenomParticipant(string $prenomParticipant): static
    {
        $this->prenomParticipant = $prenomParticipant;

        return $this;
    }

    public function getEmailParticipant(): ?string
    {
        return $this->emailParticipant;
    }

    public function setEmailParticipant(string $emailParticipant): static
    {
        $this->emailParticipant = $emailParticipant;

        return $this;
    }

    public function getTelephoneParticipant(): ?string
    {
        return $this->telephoneParticipant;
    }

    public function setTelephoneParticipant(?string $telephoneParticipant): static
    {
        $this->telephoneParticipant = $telephoneParticipant;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

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

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): static
    {
        $this->evenement = $evenement;

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
}
