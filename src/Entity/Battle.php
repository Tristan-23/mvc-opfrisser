<?php

namespace App\Entity;

use App\Enum\BattleStatus;
use App\Repository\BattleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BattleRepository::class)]
class Battle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $dateTime = null;

    #[ORM\Column(length: 20, enumType: BattleStatus::class)]
    private BattleStatus $status = BattleStatus::Scheduled;

    /** @var Collection<int, BattleParticipant> */
    #[ORM\OneToMany(targetEntity: BattleParticipant::class, mappedBy: 'battle', orphanRemoval: true)]
    private Collection $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
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

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeInterface $dateTime): static
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    public function getStatus(): BattleStatus
    {
        return $this->status;
    }

    public function setStatus(BattleStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->status === BattleStatus::Completed;
    }

    /** @return Collection<int, BattleParticipant> */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(BattleParticipant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setBattle($this);
        }
        return $this;
    }

    public function removeParticipant(BattleParticipant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            if ($participant->getBattle() === $this) {
                $participant->setBattle(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
