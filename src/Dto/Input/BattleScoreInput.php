<?php

namespace App\Dto\Input;

class BattleScoreInput
{
    public ?int $winner = null;
    public bool $isKnockout = false;
}
