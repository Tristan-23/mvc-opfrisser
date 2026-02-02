<?php

namespace App\Enum;

enum Weapon: string
{
    case SawBlade = 'saw_blade';
    case Hammer = 'hammer';
    case Flipper = 'flipper';
    case Spinner = 'spinner';
    case Crusher = 'crusher';
    case Flamethrower = 'flamethrower';
    case Axe = 'axe';
    case Wedge = 'wedge';

    public function label(): string
    {
        return match ($this) {
            self::SawBlade => 'Saw Blade',
            self::Hammer => 'Hammer',
            self::Flipper => 'Flipper',
            self::Spinner => 'Spinner',
            self::Crusher => 'Crusher',
            self::Flamethrower => 'Flamethrower',
            self::Axe => 'Axe',
            self::Wedge => 'Wedge',
        };
    }
}
