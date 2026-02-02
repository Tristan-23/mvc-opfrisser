<?php

namespace App\Repository;

use App\Entity\Battle;
use App\Enum\BattleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Battle>
 */
class BattleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Battle::class);
    }

    /** @return Battle[] */
    public function findAllWithParticipants(): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.participants', 'p')
            ->addSelect('p')
            ->leftJoin('p.robot', 'r')
            ->addSelect('r')
            ->orderBy('b.dateTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneWithParticipants(int $id): ?Battle
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.participants', 'p')
            ->addSelect('p')
            ->leftJoin('p.robot', 'r')
            ->addSelect('r')
            ->where('b.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return Battle[] */
    public function findScheduled(int $limit = 5): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.status = :status')
            ->setParameter('status', BattleStatus::Scheduled)
            ->orderBy('b.dateTime', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return Battle[] */
    public function findCompleted(int $limit = 5): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.participants', 'p')
            ->addSelect('p')
            ->leftJoin('p.robot', 'r')
            ->addSelect('r')
            ->where('b.status = :status')
            ->setParameter('status', BattleStatus::Completed)
            ->orderBy('b.dateTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function save(Battle $battle, bool $flush = false): void
    {
        $this->getEntityManager()->persist($battle);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Battle $battle, bool $flush = false): void
    {
        $this->getEntityManager()->remove($battle);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
