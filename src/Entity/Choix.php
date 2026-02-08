<?php

namespace App\Entity;

use App\Repository\ChoixRepository;
use Doctrine\ORM\Mapping as ORM;
<<<<<<< HEAD
<<<<<<< HEAD
use Symfony\Component\Validator\Constraints as Assert;
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
use Symfony\Component\Validator\Constraints as Assert;
>>>>>>> gestionquiz

#[ORM\Entity(repositoryClass: ChoixRepository::class)]
class Choix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
    #[Assert\NotBlank(message: "Le contenu du choix est requis")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le contenu doit contenir au moins {{ limit }} caractère",
        maxMessage: "Le contenu ne peut pas dépasser {{ limit }} caractères"
    )]
<<<<<<< HEAD
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
    private ?string $contenu = null;

    #[ORM\Column]
    private ?bool $estCorrect = null;

    #[ORM\ManyToOne(inversedBy: 'choix')]
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    public function getId(): ?int { return $this->id; }

    public function getContenu(): ?string { return $this->contenu; }
<<<<<<< HEAD
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
=======
>>>>>>> gestionquiz

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
        return $this;
    }

    public function isEstCorrect(): ?bool { return $this->estCorrect; }
<<<<<<< HEAD
=======

        return $this;
    }

    public function isEstCorrect(): ?bool
    {
        return $this->estCorrect;
    }
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz

    public function setEstCorrect(bool $estCorrect): static
    {
        $this->estCorrect = $estCorrect;
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
        return $this;
    }

    public function getQuestion(): ?Question { return $this->question; }
<<<<<<< HEAD
=======

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
<<<<<<< HEAD
<<<<<<< HEAD
=======

>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
        return $this;
    }
}
