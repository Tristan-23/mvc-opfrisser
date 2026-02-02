<?php

namespace App\Repository;

use App\Dto\Output\LeaderboardEntry;
use App\Dto\Output\RobotStatistics;
use App\Entity\Robot;
use App\Enum\BattleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Robot>
 */
class RobotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Robot::class);
    }

    /** @return Robot[] */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getStatistics(Robot $robot): RobotStatistics
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT
                COALESCE(SUM(CASE WHEN bp.score > 0 AND bp.score >= all_scores.max_score THEN 1 ELSE 0 END), 0) AS wins,
                COALESCE(SUM(CASE WHEN bp.score < all_scores.max_score THEN 1 ELSE 0 END), 0) AS losses,
                COALESCE(SUM(CASE WHEN bp.is_knockout = 1 THEN 1 ELSE 0 END), 0) AS knockouts,
                COUNT(bp.id) AS battles_fought,
                COALESCE(SUM(bp.score), 0) AS total_score
            FROM battle_participant bp
            INNER JOIN battle b ON b.id = bp.battle_id
            INNER JOIN (
                SELECT battle_id, MAX(score) AS max_score
                FROM battle_participant
                GROUP BY battle_id
            ) all_scores ON all_scores.battle_id = bp.battle_id
            WHERE bp.robot_id = :robotId
              AND b.status = :status
        ';

        $result = $conn->executeQuery($sql, [
            'robotId' => $robot->getId(),
            'status' => BattleStatus::Completed->value,
        ])->fetchAssociative();

        return new RobotStatistics(
            wins: (int) $result['wins'],
            losses: (int) $result['losses'],
            knockouts: (int) $result['knockouts'],
            battles_fought: (int) $result['battles_fought'],
            total_score: (int) $result['total_score'],
        );
    }

    /** @return LeaderboardEntry[] */
    public function getLeaderboard(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT
                r.id,
                r.name,
                r.owner,
                COALESCE(SUM(CASE WHEN bp.score > 0 AND bp.score >= all_scores.max_score THEN 1 ELSE 0 END), 0) AS wins,
                COALESCE(SUM(CASE WHEN bp.score < all_scores.max_score THEN 1 ELSE 0 END), 0) AS losses,
                COALESCE(SUM(CASE WHEN bp.is_knockout = 1 THEN 1 ELSE 0 END), 0) AS knockouts,
                COALESCE(SUM(bp.score), 0) AS total_score
            FROM robot r
            LEFT JOIN battle_participant bp ON bp.robot_id = r.id
            LEFT JOIN battle b ON b.id = bp.battle_id AND b.status = :status
            LEFT JOIN (
                SELECT battle_id, MAX(score) AS max_score
                FROM battle_participant
                GROUP BY battle_id
            ) all_scores ON all_scores.battle_id = bp.battle_id
            GROUP BY r.id, r.name, r.owner
            ORDER BY wins DESC, total_score DESC, r.name ASC
        ';

        $rows = $conn->executeQuery($sql, [
            'status' => BattleStatus::Completed->value,
        ])->fetchAllAssociative();

        $entries = [];
        foreach ($rows as $row) {
            $robot = $this->find($row['id']);
            if ($robot) {
                $entries[] = new LeaderboardEntry(
                    robot: $robot,
                    wins: (int) $row['wins'],
                    losses: (int) $row['losses'],
                    knockouts: (int) $row['knockouts'],
                    total_score: (int) $row['total_score'],
                );
            }
        }

        return $entries;
    }

    public function save(Robot $robot, bool $flush = false): void
    {
        $this->getEntityManager()->persist($robot);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Robot $robot, bool $flush = false): void
    {
        $this->getEntityManager()->remove($robot);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
