<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\Evaluation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }

    public function findAverageByCours(Cours $cours): ?float
    {
        $result = $this->createQueryBuilder('e')
            ->select('AVG(e.note) as average')
            ->where('e.cours = :cours')
            ->setParameter('cours', $cours)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round($result, 1) : null;
    }

    public function add(Evaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Evaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}