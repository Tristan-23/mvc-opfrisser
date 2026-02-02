<?php

namespace App\Enum;

enum Locomotion: string
{
    case Wheels = 'wheels';
    case Tracks = 'tracks';
    case Legs = 'legs';
    case Hover = 'hover';
    case Shuffle = 'shuffle';

    public function label(): string
    {
        return match ($this) {
            self::Wheels => 'Wheels',
            self::Tracks => 'Tracks',
            self::Legs => 'Legs',
            self::Hover => 'Hover',
            self::Shuffle => 'Shuffle',
        };
    }
}
