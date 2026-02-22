<?php
// src/Repository/ChatMessageRepository.php

namespace App\Repository;

use App\Entity\ChatMessage;
use App\Entity\Cours;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    public function findByCoursAndUser(Cours $cours, User $user, int $limit = 20): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.cours = :cours')
            ->andWhere('c.user = :user')
            ->setParameter('cours', $cours)
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function deleteOldMessages(int $days = 30): int
    {
        return $this->createQueryBuilder('c')
            ->delete()
            ->where('c.createdAt < :date')
            ->setParameter('date', new \DateTime("-{$days} days"))
            ->getQuery()
            ->execute();
    }
    public function findLastByUser(Cours $cours, User $user): ?ChatMessage
{
    return $this->createQueryBuilder('c')
        ->where('c.cours = :cours')
        ->andWhere('c.user = :user')
        ->setParameter('cours', $cours)
        ->setParameter('user', $user)
        ->orderBy('c.createdAt', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}
}