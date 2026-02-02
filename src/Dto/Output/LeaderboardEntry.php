<?php

namespace App\Dto\Output;

use App\Entity\Robot;

class LeaderboardEntry
{
    public function __construct(
        public readonly Robot $robot,
        public readonly int $wins,
        public readonly int $losses,
        public readonly int $knockouts,
        public readonly int $total_score,
    ) {}
}
