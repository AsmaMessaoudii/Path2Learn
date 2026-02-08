<?php

namespace App\Entity;

use App\Repository\ChoixRepository;
use Doctrine\ORM\Mapping as ORM;
<<<<<<< HEAD
use Symfony\Component\Validator\Constraints as Assert;
=======
<<<<<<< HEAD
<<<<<<< HEAD
use Symfony\Component\Validator\Constraints as Assert;
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
use Symfony\Component\Validator\Constraints as Assert;
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc

#[ORM\Entity(repositoryClass: ChoixRepository::class)]
class Choix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
<<<<<<< HEAD
=======
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
    #[Assert\NotBlank(message: "Le contenu du choix est requis")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le contenu doit contenir au moins {{ limit }} caractère",
        maxMessage: "Le contenu ne peut pas dépasser {{ limit }} caractères"
    )]
<<<<<<< HEAD
=======
<<<<<<< HEAD
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
    private ?string $contenu = null;

    #[ORM\Column]
    private ?bool $estCorrect = null;

    #[ORM\ManyToOne(inversedBy: 'choix')]
<<<<<<< HEAD
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

=======
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

>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }
<<<<<<< HEAD
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
<<<<<<< HEAD
=======
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
        return $this;
    }

    public function isEstCorrect(): ?bool { return $this->estCorrect; }
<<<<<<< HEAD
=======

>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
        return $this;
    }

    public function isEstCorrect(): ?bool
    {
        return $this->estCorrect;
    }
<<<<<<< HEAD
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc

    public function setEstCorrect(bool $estCorrect): static
    {
        $this->estCorrect = $estCorrect;
<<<<<<< HEAD
=======
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
        return $this;
    }

    public function getQuestion(): ?Question { return $this->question; }
<<<<<<< HEAD
=======

>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }
<<<<<<< HEAD
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
<<<<<<< HEAD
=======
<<<<<<< HEAD
<<<<<<< HEAD
=======

>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
        return $this;
    }
}
