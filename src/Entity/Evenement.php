<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateEvent = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $heureEvent = null;

    #[ORM\Column(length: 150)]
    private ?string $lieu = null;

    #[ORM\Column(length: 50)]
    private ?string $typeEvent = null;

    #[ORM\ManyToOne(inversedBy: 'evenement')]
    private ?User $user = null;

    /**
     * @var Collection<int, ParticipationEvent>
     */
    #[ORM\OneToMany(targetEntity: ParticipationEvent::class, mappedBy: 'evenement')]
    private Collection $participationEvent;

    public function __construct()
    {
        $this->participationEvent = new ArrayCollection();
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

    public function getDateEvent(): ?\DateTime
    {
        return $this->dateEvent;
    }

    public function setDateEvent(\DateTime $dateEvent): static
    {
        $this->dateEvent = $dateEvent;

        return $this;
    }

    public function getHeureEvent(): ?\DateTime
    {
        return $this->heureEvent;
    }

    public function setHeureEvent(\DateTime $heureEvent): static
    {
        $this->heureEvent = $heureEvent;

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

    public function getTypeEvent(): ?string
    {
        return $this->typeEvent;
    }

    public function setTypeEvent(string $typeEvent): static
    {
        $this->typeEvent = $typeEvent;

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
}
