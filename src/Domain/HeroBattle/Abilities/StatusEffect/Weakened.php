<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\StatusEffect;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesDefense;
use OpenDominion\Models\HeroCombatant;

/**
 * Weakened Status Effect
 *
 * Reduces defense by 15 (typically applied by enemy abilities)
 */
class Weakened extends AbstractAbility implements ModifiesDefense
{
    /**
     * Reduce defense when weakened
     */
    public function modifyDefense(HeroCombatant $combatant, int $currentDefense): int
    {
        return $currentDefense - 15;
    }
}
