<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
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
    private ?User $user = null;

    /**
     * @var Collection<int, RessourcePedagogique>
     */
    #[ORM\OneToMany(targetEntity: RessourcePedagogique::class, mappedBy: 'cours')]
    private Collection $ressourcePedagogiques;

    public function __construct()
    {
        $this->ressourcePedagogiques = new ArrayCollection();
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

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getMatiere(): ?string
    {
        return $this->matiere;
    }

    public function setMatiere(string $matiere): static
    {
        $this->matiere = $matiere;

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

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getEmailProf(): ?string
    {
        return $this->emailProf;
    }

    public function setEmailProf(string $emailProf): static
    {
        $this->emailProf = $emailProf;

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

        return $this;
    }

    public function removeRessourcePedagogique(RessourcePedagogique $ressourcePedagogique): static
    {
        if ($this->ressourcePedagogiques->removeElement($ressourcePedagogique)) {
            // set the owning side to null (unless already changed)
            if ($ressourcePedagogique->getCours() === $this) {
                $ressourcePedagogique->setCours(null);
            }
        }

        return $this;
    }
}
