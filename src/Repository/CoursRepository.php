<?php

namespace App\Repository;

use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

<<<<<<< HEAD

=======
<<<<<<< HEAD
=======
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
>>>>>>> origin/main
    //    /**
    //     * @return Cours[] Returns an array of Cours objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cours
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
<<<<<<< HEAD


=======
}
<<<<<<< HEAD
=======
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
    public function getStatisticsByStatus(): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.statut, COUNT(c.id) as count')
            ->groupBy('c.statut')
            ->getQuery();

        return $query->getResult();
    }

    public function findAllSorted(string $sortBy = 'titre', string $direction = 'ASC'): array
    {
        $validSorts = ['titre', 'matiere', 'niveau', 'duree', 'statut', 'dateCreation', 'emailProf'];
        $validDirections = ['ASC', 'DESC'];

        if (!in_array($sortBy, $validSorts)) {
            $sortBy = 'titre';
        }

        if (!in_array($direction, $validDirections)) {
            $direction = 'ASC';
        }

        // Tri spécial pour le niveau (ordre logique)
        if ($sortBy === 'niveau') {
            return $this->findAllSortedByNiveau($direction);
        }

        // Tri normal pour les autres champs
        return $this->createQueryBuilder('c')
            ->orderBy('c.' . $sortBy, $direction)
            ->addOrderBy('c.titre', 'ASC') // Second tri pour consistance
            ->getQuery()
            ->getResult();
    }

    /**
     * Tri spécial pour le niveau avec ordre logique
     */
    private function findAllSortedByNiveau(string $direction = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('c');
        
        // Ordre logique des niveaux
        $niveauOrder = [
            'Débutant' => 1,
            'Intermédiaire' => 2,
            'Avancé' => 3
        ];
        
        // Créer une expression CASE pour ordonner logiquement
        $caseExpr = 'CASE c.niveau ';
        foreach ($niveauOrder as $niveau => $order) {
            $caseExpr .= "WHEN '{$niveau}' THEN {$order} ";
        }
        $caseExpr .= 'ELSE 99 END';
        
        if ($direction === 'ASC') {
            $qb->orderBy($caseExpr, 'ASC');
        } else {
            $qb->orderBy($caseExpr, 'DESC');
        }
        
        // Second tri par titre pour consistance
        $qb->addOrderBy('c.titre', 'ASC');
        
        return $qb->getQuery()->getResult();
    }

    public function search(string $searchTerm): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.titre LIKE :term')
            ->orWhere('c.matiere LIKE :term')
            ->orWhere('c.description LIKE :term')
            ->orWhere('c.niveau LIKE :term')
            ->orWhere('c.statut LIKE :term')
            ->orWhere('c.emailProf LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('c.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function searchCourses(array $criteria = []): array
    {
        $qb = $this->createQueryBuilder('c');
        
        // Filtre par statut par défaut
        if (isset($criteria['statut'])) {
            $qb->andWhere('c.statut = :statut')
               ->setParameter('statut', $criteria['statut']);
        }
        
        // Recherche par texte
        if (!empty($criteria['search'])) {
            $searchTerm = '%' . $criteria['search'] . '%';
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('c.titre', ':search'),
                $qb->expr()->like('c.description', ':search'),
                $qb->expr()->like('c.matiere', ':search'),
                $qb->expr()->like('c.emailProf', ':search')
            ))
            ->setParameter('search', $searchTerm);
        }
        
        // Filtre par matière
        if (!empty($criteria['matiere'])) {
            $qb->andWhere('c.matiere = :matiere')
               ->setParameter('matiere', $criteria['matiere']);
        }
        
        // Filtre par niveau
        if (!empty($criteria['niveau'])) {
            $qb->andWhere('c.niveau = :niveau')
               ->setParameter('niveau', $criteria['niveau']);
        }
        
        // Tri par date de création
        $qb->orderBy('c.dateCreation', 'DESC');
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les matières distinctes
     */
    public function findDistinctMatieres(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('DISTINCT c.matiere')
            ->where('c.statut = :statut')
            ->setParameter('statut', 'publié')
            ->orderBy('c.matiere', 'ASC')
            ->getQuery();
        
        $results = $qb->getArrayResult();
        return array_column($results, 'matiere');
    }

    /**
     * Récupère les niveaux distincts
     */
    public function findDistinctNiveaux(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('DISTINCT c.niveau')
            ->where('c.statut = :statut')
            ->andWhere('c.niveau IS NOT NULL')
            ->setParameter('statut', 'publié')
            ->orderBy('c.niveau', 'ASC')
            ->getQuery();
        
        $results = $qb->getArrayResult();
        return array_column($results, 'niveau');
    }
    /**
 * Recherche dans les cours publiés
 */
public function searchPublishedCourses(string $searchTerm): array
{
    return $this->createQueryBuilder('c')
        ->where('c.statut = :statut')
        ->andWhere(
            'c.titre LIKE :search OR 
             c.description LIKE :search OR 
             c.matiere LIKE :search OR 
             c.emailProf LIKE :search OR 
             c.niveau LIKE :search'
        )
        ->setParameter('statut', 'publié')
        ->setParameter('search', '%' . $searchTerm . '%')
        ->orderBy('c.dateCreation', 'DESC')
        ->getQuery()
        ->getResult();
}
}
<<<<<<< HEAD

=======
<<<<<<< HEAD
=======
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
>>>>>>> 69dc488ab7d7f905f62c0b521f445bd5cc7ca6fc
>>>>>>> origin/main
