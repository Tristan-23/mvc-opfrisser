<?php

namespace App\Enum;

enum BattleStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Completed => 'Completed',
        };
    }
}
