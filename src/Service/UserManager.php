<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Question;
use App\Entity\Cours;
use App\Entity\Evenement;
use App\Entity\Portfolio;

class UserManager
{
    // Validate method for multiple entities
    public function validate(object $entity): bool
    {
        if ($entity instanceof User) {
            if (empty($entity->getNom())) {
                throw new \InvalidArgumentException('Le nom est obligatoire');
            }
            if (empty($entity->getPrenom())) {
                throw new \InvalidArgumentException('Le prenom est obligatoire');
            }
            if (!filter_var($entity->getEmail(), FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Email invalide');
            }
        }

        if ($entity instanceof Question) {
            if (empty($entity->getTitre())) {
                throw new \InvalidArgumentException('Le titre de la question est obligatoire');
            }
            if ($entity->getDuree() <= 0) {
                throw new \InvalidArgumentException('La durée de la question doit être positive');
            }
        }

        if ($entity instanceof Cours) {
            if (empty($entity->getTitre())) {
                throw new \InvalidArgumentException('Le titre du cours est obligatoire');
            }
            if ($entity->getDuree() <= 0) {
                throw new \InvalidArgumentException('La durée du cours doit être positive');
            }
        }

        if ($entity instanceof Evenement) {
            if (empty($entity->getTitre())) {
                throw new \InvalidArgumentException('Le titre de l’événement est obligatoire');
            }
            if ($entity->getCapaciteMax() <= 0) {
                throw new \InvalidArgumentException('La capacité de l’événement doit être positive');
            }
        }

        if ($entity instanceof Portfolio) {
            if (empty($entity->getTitre())) {
                throw new \InvalidArgumentException('Le titre du portfolio est obligatoire');
            }
            if (empty($entity->getDescription())) {
                throw new \InvalidArgumentException('La description du portfolio est obligatoire');
            }
        }

        return true;
    }
}
