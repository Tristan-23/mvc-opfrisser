<?php

namespace App\Entity;

use App\Enum\Locomotion;
use App\Enum\Weapon;
use App\Repository\RobotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RobotRepository::class)]
class Robot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $owner = null;

    #[ORM\Column(length: 50, enumType: Weapon::class)]
    #[Assert\NotNull]
    private ?Weapon $weapon = null;

    #[ORM\Column(length: 50, enumType: Locomotion::class)]
    #[Assert\NotNull]
    private ?Locomotion $locomotion = null;

    /** @var Collection<int, BattleParticipant> */
    #[ORM\OneToMany(targetEntity: BattleParticipant::class, mappedBy: 'robot', orphanRemoval: true)]
    private Collection $battleParticipants;

    public function __construct()
    {
        $this->battleParticipants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    public function getWeapon(): ?Weapon
    {
        return $this->weapon;
    }

    public function setWeapon(Weapon $weapon): static
    {
        $this->weapon = $weapon;
        return $this;
    }

    public function getLocomotion(): ?Locomotion
    {
        return $this->locomotion;
    }

    public function setLocomotion(Locomotion $locomotion): static
    {
        $this->locomotion = $locomotion;
        return $this;
    }

    /** @return Collection<int, BattleParticipant> */
    public function getBattleParticipants(): Collection
    {
        return $this->battleParticipants;
    }

    public function addBattleParticipant(BattleParticipant $participant): static
    {
        if (!$this->battleParticipants->contains($participant)) {
            $this->battleParticipants->add($participant);
            $participant->setRobot($this);
        }
        return $this;
    }

    public function removeBattleParticipant(BattleParticipant $participant): static
    {
        if ($this->battleParticipants->removeElement($participant)) {
            if ($participant->getRobot() === $this) {
                $participant->setRobot(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
