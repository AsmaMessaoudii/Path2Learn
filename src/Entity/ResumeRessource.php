<?php

namespace App\Entity;

use App\Repository\ResumeRessourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResumeRessourceRepository::class)]
class ResumeRessource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?RessourcePedagogique $ressource = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenuTexte = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resumeCourt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resumeDetaille = null;

    #[ORM\Column(nullable: true)]
    private ?array $motsCles = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombrePages = null;

    #[ORM\Column(nullable: true)]
    private ?int $dureeVideo = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $langue = null;

    #[ORM\Column]
    private ?\DateTime $dateAnalyse = null;

    #[ORM\Column]
    private ?bool $analyseComplete = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRessource(): ?RessourcePedagogique
    {
        return $this->ressource;
    }

    public function setRessource(?RessourcePedagogique $ressource): static
    {
        $this->ressource = $ressource;

        return $this;
    }

    public function getContenuTexte(): ?string
    {
        return $this->contenuTexte;
    }

    public function setContenuTexte(?string $contenuTexte): static
    {
        $this->contenuTexte = $contenuTexte;

        return $this;
    }

    public function getResumeCourt(): ?string
    {
        return $this->resumeCourt;
    }

    public function setResumeCourt(?string $resumeCourt): static
    {
        $this->resumeCourt = $resumeCourt;

        return $this;
    }

    public function getResumeDetaille(): ?string
    {
        return $this->resumeDetaille;
    }

    public function setResumeDetaille(?string $resumeDetaille): static
    {
        $this->resumeDetaille = $resumeDetaille;

        return $this;
    }

    public function getMotsCles(): ?array
    {
        return $this->motsCles;
    }

    public function setMotsCles(?array $motsCles): static
    {
        $this->motsCles = $motsCles;

        return $this;
    }

    public function getNombrePages(): ?int
    {
        return $this->nombrePages;
    }

    public function setNombrePages(?int $nombrePages): static
    {
        $this->nombrePages = $nombrePages;

        return $this;
    }

    public function getDureeVideo(): ?int
    {
        return $this->dureeVideo;
    }

    public function setDureeVideo(?int $dureeVideo): static
    {
        $this->dureeVideo = $dureeVideo;

        return $this;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(?string $langue): static
    {
        $this->langue = $langue;

        return $this;
    }

    public function getDateAnalyse(): ?\DateTime
    {
        return $this->dateAnalyse;
    }

    public function setDateAnalyse(\DateTime $dateAnalyse): static
    {
        $this->dateAnalyse = $dateAnalyse;

        return $this;
    }

    public function isAnalyseComplete(): ?bool
    {
        return $this->analyseComplete;
    }

    public function setAnalyseComplete(bool $analyseComplete): static
    {
        $this->analyseComplete = $analyseComplete;

        return $this;
    }
}
