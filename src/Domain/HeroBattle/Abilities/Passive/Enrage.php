<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesAttack;
use OpenDominion\Models\HeroCombatant;

/**
 * Enrage Ability
 *
 * Increases attack by 10 when health is low (40 or below)
 */
class Enrage extends AbstractAbility implements ModifiesAttack
{
    /**
     * Modify attack when health is low
     */
    public function modifyAttack(HeroCombatant $combatant, int $currentAttack): int
    {
        if ($combatant->current_health <= 40) {
            return $currentAttack + 10;
        }

        return $currentAttack;
    }
}
