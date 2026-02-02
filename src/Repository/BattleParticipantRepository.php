<?php

namespace App\Repository;

use App\Entity\BattleParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BattleParticipant>
 */
class BattleParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BattleParticipant::class);
    }

    public function save(BattleParticipant $participant, bool $flush = false): void
    {
        $this->getEntityManager()->persist($participant);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BattleParticipant $participant, bool $flush = false): void
    {
        $this->getEntityManager()->remove($participant);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
