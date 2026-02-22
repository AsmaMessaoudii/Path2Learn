<?php

namespace App\Repository;

use App\Entity\BadRatingReason;
use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BadRatingReason>
 */
class BadRatingReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BadRatingReason::class);
    }

    /**
     * Trouver toutes les raisons pour un cours spécifique
     */
    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.evaluation', 'e')
            ->where('e.cours = :course')
            ->setParameter('course', $course)
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter le nombre de mauvaises notes pour un cours
     */
    public function countBadRatingsByCourse(Course $course): int
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->join('b.evaluation', 'e')
            ->where('e.cours = :course')
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Statistiques des raisons pour un cours
     */
    public function getReasonStatisticsByCourse(Course $course): array
    {
        $results = $this->createQueryBuilder('b')
            ->select('b.reason, COUNT(b.id) as count')
            ->join('b.evaluation', 'e')
            ->where('e.cours = :course')
            ->setParameter('course', $course)
            ->groupBy('b.reason')
            ->getQuery()
            ->getResult();

        $statistics = [];
        foreach ($results as $result) {
            $statistics[$result['reason']] = $result['count'];
        }

        return $statistics;
    }
}