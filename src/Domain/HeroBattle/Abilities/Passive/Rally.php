<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesDefense;
use OpenDominion\Models\HeroCombatant;

/**
 * Rally Ability
 *
 * Increases defense by 5 when health is low (40 or below)
 */
class Rally extends AbstractAbility implements ModifiesDefense
{
    /**
     * Modify defense when health is low
     */
    public function modifyDefense(HeroCombatant $combatant, int $currentDefense): int
    {
        if ($combatant->current_health <= 40) {
            return $currentDefense + 5;
        }

        return $currentDefense;
    }
}
