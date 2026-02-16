<?php
// src/Repository/ProjetRepository.php

namespace App\Repository;

use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    /**
     * Recherche et tri des projets
     */
    public function findByFilters(
        int $portfolioId,
        string $searchTerm = '',
        string $sortBy = 'date_desc',
        string $technologie = ''
    ): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.portfolio = :portfolioId')
            ->setParameter('portfolioId', $portfolioId);

        // Filtre par recherche
        if (!empty($searchTerm)) {
            $qb->andWhere('p.titreProjet LIKE :search OR p.description LIKE :search OR p.text LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        // Filtre par technologie
        if (!empty($technologie)) {
            $qb->andWhere('p.technologies LIKE :technologie')
                ->setParameter('technologie', '%' . $technologie . '%');
        }

        // Tri des résultats - Utilise dateRealisation au lieu de dateCreation
        switch ($sortBy) {
            case 'date_asc':
                $qb->orderBy('p.dateRealisation', 'ASC');
                break;
            case 'date_desc':
                $qb->orderBy('p.dateRealisation', 'DESC');
                break;
            case 'title_asc':
                $qb->orderBy('p.titreProjet', 'ASC');
                break;
            case 'title_desc':
                $qb->orderBy('p.titreProjet', 'DESC');
                break;
            default:
                $qb->orderBy('p.dateRealisation', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouver toutes les technologies distinctes dans un portfolio
     */
    public function findDistinctTechnologies(int $portfolioId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p.technologies')
            ->andWhere('p.portfolio = :portfolioId')
            ->setParameter('portfolioId', $portfolioId)
            ->orderBy('p.technologies', 'ASC');

        $results = $qb->getQuery()->getResult();
        
        // Extraire et nettoyer les technologies
        $technologies = [];
        foreach ($results as $result) {
            if ($result['technologies']) {
                $techs = explode(',', $result['technologies']);
                foreach ($techs as $tech) {
                    $tech = trim($tech);
                    if (!empty($tech) && !in_array($tech, $technologies)) {
                        $technologies[] = $tech;
                    }
                }
            }
        }
        
        sort($technologies);
        return $technologies;
    }
}