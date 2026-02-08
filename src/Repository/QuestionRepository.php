<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> gestionquiz
    /**
     * Recherche des questions selon différents critères avec tri
     */
    public function searchByCriteria(
        string $searchTerm, 
        string $searchType = 'titre',
        string $sortBy = 'titre',
        string $sortOrder = 'asc'
    ): array
    {
        $queryBuilder = $this->createQueryBuilder('q')
            ->leftJoin('q.user', 'u');
        
        // Appliquer le tri (uniquement les options demandées)
        switch ($sortBy) {
            case 'titre':
                $queryBuilder->orderBy('q.titre', $sortOrder === 'desc' ? 'DESC' : 'ASC');
                break;
            case 'duree':
                $queryBuilder->orderBy('q.duree', $sortOrder === 'desc' ? 'DESC' : 'ASC');
                break;
            case 'noteMax':
                $queryBuilder->orderBy('q.noteMax', $sortOrder === 'desc' ? 'DESC' : 'ASC');
                break;
            default:
                $queryBuilder->orderBy('q.titre', 'ASC');
        }

        if (!empty($searchTerm)) {
            switch ($searchType) {
                case 'titre':
                    $queryBuilder->andWhere('q.titre LIKE :searchTerm')
                        ->setParameter('searchTerm', '%' . $searchTerm . '%');
                    break;
                    
                case 'duree':
                    if (is_numeric($searchTerm)) {
                        $queryBuilder->andWhere('q.duree = :duree')
                            ->setParameter('duree', (int)$searchTerm);
                    }
                    break;
                    
                case 'noteMax':
                    if (is_numeric($searchTerm)) {
                        $queryBuilder->andWhere('q.noteMax = :noteMax')
                            ->setParameter('noteMax', (float)$searchTerm);
                    }
                    break;
                    
                case 'description':
                    $queryBuilder->andWhere('q.description LIKE :searchTerm')
                        ->setParameter('searchTerm', '%' . $searchTerm . '%');
                    break;
                    
                case 'utilisateur':
                    $queryBuilder->andWhere('u.username LIKE :searchTerm')
                        ->setParameter('searchTerm', '%' . $searchTerm . '%');
                    break;
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
     public function getCorrectAnswersStats(): array
    {
        return $this->createQueryBuilder('q')
            ->select([
                'q.id',
                'q.titre',
                'COUNT(c.id) as totalChoix',
                'SUM(CASE WHEN c.estCorrect = true THEN 1 ELSE 0 END) as bonnesReponses'
            ])
            ->leftJoin('q.choix', 'c')
            ->groupBy('q.id')
            ->orderBy('q.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Récupère les statistiques détaillées pour une question spécifique
     */
    public function getQuestionStats(int $questionId): array
    {
        return $this->createQueryBuilder('q')
            ->select([
                'q.id',
                'q.titre',
                'q.description',
                'COUNT(c.id) as totalChoix',
                'SUM(CASE WHEN c.estCorrect = true THEN 1 ELSE 0 END) as bonnesReponses',
                'SUM(CASE WHEN c.estCorrect = false THEN 1 ELSE 0 END) as mauvaisesReponses'
            ])
            ->leftJoin('q.choix', 'c')
            ->where('q.id = :questionId')
            ->setParameter('questionId', $questionId)
            ->groupBy('q.id')
            ->getQuery()
            ->getOneOrNullResult();
    }

    
<<<<<<< HEAD
}
=======
    //    /**
    //     * @return Question[] Returns an array of Question objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Question
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
}
>>>>>>> gestionquiz
