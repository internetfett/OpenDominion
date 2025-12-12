<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesDefense;
use OpenDominion\Models\HeroCombatant;

/**
 * Arcane Shield Ability
 *
 * Provides a constant +10 defense boost
 */
class ArcaneShield extends AbstractAbility implements ModifiesDefense
{
    /**
     * Add defense bonus
     */
    public function modifyDefense(HeroCombatant $combatant, int $currentDefense): int
    {
        return $currentDefense + 10;
    }
}
