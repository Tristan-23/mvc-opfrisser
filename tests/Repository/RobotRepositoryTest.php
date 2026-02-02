<?php

namespace App\Tests\Repository;

use App\Entity\Battle;
use App\Entity\BattleParticipant;
use App\Entity\Robot;
use App\Enum\BattleStatus;
use App\Enum\Locomotion;
use App\Enum\Weapon;
use App\Repository\RobotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RobotRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private RobotRepository $robotRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->robotRepository = $this->em->getRepository(Robot::class);

        // Clean tables
        $this->em->getConnection()->executeStatement('DELETE FROM battle_participant');
        $this->em->getConnection()->executeStatement('DELETE FROM battle');
        $this->em->getConnection()->executeStatement('DELETE FROM robot');
    }

    private function createRobot(string $name, string $owner = 'Owner'): Robot
    {
        $robot = new Robot();
        $robot->setName($name);
        $robot->setOwner($owner);
        $robot->setWeapon(Weapon::Hammer);
        $robot->setLocomotion(Locomotion::Wheels);
        $this->em->persist($robot);
        return $robot;
    }

    private function createBattle(string $name, BattleStatus $status = BattleStatus::Completed): Battle
    {
        $battle = new Battle();
        $battle->setName($name);
        $battle->setDateTime(new \DateTime());
        $battle->setStatus($status);
        $this->em->persist($battle);
        return $battle;
    }

    private function addParticipant(Battle $battle, Robot $robot, int $score = 0, bool $knockout = false): BattleParticipant
    {
        $bp = new BattleParticipant();
        $bp->setBattle($battle);
        $bp->setRobot($robot);
        $bp->setScore($score);
        $bp->setIsKnockout($knockout);
        $this->em->persist($bp);
        return $bp;
    }

    public function testGetStatisticsWithWinsAndLosses(): void
    {
        $robot1 = $this->createRobot('ChampBot');
        $robot2 = $this->createRobot('LoserBot');

        // Battle 1: robot1 wins
        $battle1 = $this->createBattle('Battle 1');
        $this->addParticipant($battle1, $robot1, 3, false);
        $this->addParticipant($battle1, $robot2, 0, false);

        // Battle 2: robot1 wins by knockout
        $battle2 = $this->createBattle('Battle 2');
        $this->addParticipant($battle2, $robot1, 3, true);
        $this->addParticipant($battle2, $robot2, 0, false);

        // Battle 3: robot2 wins
        $battle3 = $this->createBattle('Battle 3');
        $this->addParticipant($battle3, $robot1, 0, false);
        $this->addParticipant($battle3, $robot2, 3, false);

        $this->em->flush();

        $stats = $this->robotRepository->getStatistics($robot1);

        $this->assertSame(2, $stats->wins);
        $this->assertSame(1, $stats->losses);
        $this->assertSame(1, $stats->knockouts);
        $this->assertSame(3, $stats->battles_fought);
        $this->assertSame(6, $stats->total_score);
    }

    public function testScheduledBattlesExcludedFromStatistics(): void
    {
        $robot1 = $this->createRobot('TestBot');
        $robot2 = $this->createRobot('OpponentBot');

        // Completed battle: robot1 wins
        $completedBattle = $this->createBattle('Completed Battle', BattleStatus::Completed);
        $this->addParticipant($completedBattle, $robot1, 3, false);
        $this->addParticipant($completedBattle, $robot2, 0, false);

        // Scheduled battle: should NOT be counted
        $scheduledBattle = $this->createBattle('Scheduled Battle', BattleStatus::Scheduled);
        $this->addParticipant($scheduledBattle, $robot1, 0, false);
        $this->addParticipant($scheduledBattle, $robot2, 0, false);

        $this->em->flush();

        $stats = $this->robotRepository->getStatistics($robot1);

        $this->assertSame(1, $stats->wins);
        $this->assertSame(0, $stats->losses);
        $this->assertSame(1, $stats->battles_fought);
    }

    public function testGetStatisticsWithNoBattles(): void
    {
        $robot = $this->createRobot('LonelyBot');
        $this->em->flush();

        $stats = $this->robotRepository->getStatistics($robot);

        $this->assertSame(0, $stats->wins);
        $this->assertSame(0, $stats->losses);
        $this->assertSame(0, $stats->knockouts);
        $this->assertSame(0, $stats->battles_fought);
        $this->assertSame(0, $stats->total_score);
    }

    public function testGetLeaderboardOrder(): void
    {
        $robot1 = $this->createRobot('TopBot');
        $robot2 = $this->createRobot('MidBot');
        $robot3 = $this->createRobot('BotBot');

        // robot1 wins both battles
        $battle1 = $this->createBattle('Battle 1');
        $this->addParticipant($battle1, $robot1, 3, false);
        $this->addParticipant($battle1, $robot2, 0, false);

        $battle2 = $this->createBattle('Battle 2');
        $this->addParticipant($battle2, $robot1, 3, false);
        $this->addParticipant($battle2, $robot3, 0, false);

        // robot2 wins one battle
        $battle3 = $this->createBattle('Battle 3');
        $this->addParticipant($battle3, $robot2, 3, false);
        $this->addParticipant($battle3, $robot3, 0, false);

        $this->em->flush();

        $leaderboard = $this->robotRepository->getLeaderboard();

        $this->assertSame('TopBot', $leaderboard[0]->robot->getName());
        $this->assertSame(2, $leaderboard[0]->wins);
        $this->assertSame('MidBot', $leaderboard[1]->robot->getName());
        $this->assertSame(1, $leaderboard[1]->wins);
    }
}
