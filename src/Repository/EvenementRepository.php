<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * @return Evenement[]
     */
    public function findEventsByFilters(array $filters, int $page = 1, int $limit = 5): array
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.dateDebut', 'DESC');

        if (!empty($filters['q'])) {
            $qb->andWhere('e.titre LIKE :q OR e.lieu LIKE :q')
               ->setParameter('q', '%' . $filters['q'] . '%');
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('e.categorie = :category')
               ->setParameter('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('e.statut = :status')
               ->setParameter('status', $filters['status']);
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countEventsByFilters(array $filters): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        if (!empty($filters['q'])) {
            $qb->andWhere('e.titre LIKE :q OR e.lieu LIKE :q')
               ->setParameter('q', '%' . $filters['q'] . '%');
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('e.categorie = :category')
               ->setParameter('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('e.statut = :status')
               ->setParameter('status', $filters['status']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
