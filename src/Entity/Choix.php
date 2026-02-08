<?php

namespace App\Entity;

use App\Repository\ChoixRepository;
use Doctrine\ORM\Mapping as ORM;
<<<<<<< HEAD
use Symfony\Component\Validator\Constraints as Assert;
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37

#[ORM\Entity(repositoryClass: ChoixRepository::class)]
class Choix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
<<<<<<< HEAD
    #[Assert\NotBlank(message: "Le contenu du choix est requis")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le contenu doit contenir au moins {{ limit }} caractère",
        maxMessage: "Le contenu ne peut pas dépasser {{ limit }} caractères"
    )]
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
    private ?string $contenu = null;

    #[ORM\Column]
    private ?bool $estCorrect = null;

    #[ORM\ManyToOne(inversedBy: 'choix')]
<<<<<<< HEAD
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    public function getId(): ?int { return $this->id; }

    public function getContenu(): ?string { return $this->contenu; }
=======
    private ?Question $question = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
<<<<<<< HEAD
        return $this;
    }

    public function isEstCorrect(): ?bool { return $this->estCorrect; }
=======

        return $this;
    }

    public function isEstCorrect(): ?bool
    {
        return $this->estCorrect;
    }
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37

    public function setEstCorrect(bool $estCorrect): static
    {
        $this->estCorrect = $estCorrect;
<<<<<<< HEAD
        return $this;
    }

    public function getQuestion(): ?Question { return $this->question; }
=======

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
<<<<<<< HEAD
=======

>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
        return $this;
    }
}
