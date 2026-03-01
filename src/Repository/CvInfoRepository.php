<?php

namespace App\Repository;

use App\Entity\CvInfo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CvInfo>
 */
class CvInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CvInfo::class);
    }

    public function findOneByUser(User $user): ?CvInfo
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.updated_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOrCreateForUser(User $user): CvInfo
    {
        $cvInfo = $this->findOneByUser($user);
        
        if (!$cvInfo) {
            $cvInfo = new CvInfo();
            $cvInfo->setUser($user);
        }
        
        return $cvInfo;
    }

    public function save(CvInfo $cvInfo, bool $flush = true): void
    {
        $cvInfo->setUpdatedAt(new \DateTimeImmutable());
        $this->getEntityManager()->persist($cvInfo);
        
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}