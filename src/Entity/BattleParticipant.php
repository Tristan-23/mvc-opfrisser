<?php

namespace App\Entity;

use App\Repository\BattleParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BattleParticipantRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_battle_robot', columns: ['battle_id', 'robot_id'])]
class BattleParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Battle::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Battle $battle = null;

    #[ORM\ManyToOne(targetEntity: Robot::class, inversedBy: 'battleParticipants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Robot $robot = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $score = 0;

    #[ORM\Column]
    private bool $isKnockout = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBattle(): ?Battle
    {
        return $this->battle;
    }

    public function setBattle(?Battle $battle): static
    {
        $this->battle = $battle;
        return $this;
    }

    public function getRobot(): ?Robot
    {
        return $this->robot;
    }

    public function setRobot(?Robot $robot): static
    {
        $this->robot = $robot;
        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;
        return $this;
    }

    public function isKnockout(): bool
    {
        return $this->isKnockout;
    }

    public function setIsKnockout(bool $isKnockout): static
    {
        $this->isKnockout = $isKnockout;
        return $this;
    }
}
