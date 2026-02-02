<?php

namespace App\Dto\Output;

class RobotStatistics
{
    public function __construct(
        public readonly int $wins,
        public readonly int $losses,
        public readonly int $knockouts,
        public readonly int $battles_fought,
        public readonly int $total_score,
    ) {}
}
