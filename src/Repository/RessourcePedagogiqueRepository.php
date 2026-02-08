<?php

namespace App\Repository;

use App\Entity\RessourcePedagogique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RessourcePedagogique>
 */
class RessourcePedagogiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RessourcePedagogique::class);
    }

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
    //    /**
    //     * @return RessourcePedagogique[] Returns an array of RessourcePedagogique objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RessourcePedagogique
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
<<<<<<< HEAD
=======
    public function findAllSorted(string $sortBy = 'titre', string $direction = 'ASC'): array
    {
        $validSorts = ['titre', 'type', 'dateAjout'];
        $validDirections = ['ASC', 'DESC'];

        if (!in_array($sortBy, $validSorts)) {
            $sortBy = 'titre';
        }

        if (!in_array($direction, $validDirections)) {
            $direction = 'ASC';
        }

        return $this->createQueryBuilder('r')
            ->leftJoin('r.cours', 'c')
            ->addSelect('c')
            ->orderBy('r.' . $sortBy, $direction)
            ->getQuery()
            ->getResult();
    }

    public function search(string $searchTerm): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.cours', 'c')
            ->addSelect('c')
            ->where('r.titre LIKE :term')
            ->orWhere('r.type LIKE :term')
            ->orWhere('r.url LIKE :term')
            ->orWhere('c.titre LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('r.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
