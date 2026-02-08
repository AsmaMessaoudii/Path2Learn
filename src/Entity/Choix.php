<?php

namespace App\Entity;

use App\Repository\ChoixRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChoixRepository::class)]
class Choix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le contenu du choix est requis")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le contenu doit contenir au moins {{ limit }} caractère",
        maxMessage: "Le contenu ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $contenu = null;

    #[ORM\Column]
    private ?bool $estCorrect = null;

    #[ORM\ManyToOne(inversedBy: 'choix')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    public function getId(): ?int { return $this->id; }

    public function getContenu(): ?string { return $this->contenu; }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function isEstCorrect(): ?bool { return $this->estCorrect; }

    public function setEstCorrect(bool $estCorrect): static
    {
        $this->estCorrect = $estCorrect;
        return $this;
    }

    public function getQuestion(): ?Question { return $this->question; }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
        return $this;
    }
}
