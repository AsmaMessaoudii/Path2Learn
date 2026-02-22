<?php

namespace App\Repository;

use App\Entity\CourseSummary;
use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CourseSummary>
 */
class CourseSummaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseSummary::class);
    }

    /**
     * Trouve le dernier résumé d'un cours
     */
    public function findLatestByCourse(Cours $cours): ?CourseSummary
    {
        return $this->createQueryBuilder('cs')
            ->where('cs.cours = :cours')
            ->setParameter('cours', $cours)
            ->orderBy('cs.generatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Supprime les anciens résumés (plus vieux que X jours)
     */
    public function deleteOldSummaries(int $days = 30): int
    {
        $date = new \DateTime("-{$days} days");
        
        return $this->createQueryBuilder('cs')
            ->delete()
            ->where('cs.generatedAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}