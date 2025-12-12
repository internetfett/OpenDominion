<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Special;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesDefense;
use OpenDominion\Models\HeroCombatant;

/**
 * Undying Legion Ability
 *
 * Sets defense to 999 while any minions (non-hero combatants) are alive
 * Used by boss enemies that are protected by their minions
 */
class UndyingLegion extends AbstractAbility implements ModifiesDefense
{
    /**
     * Set defense to 999 if any minions are alive
     */
    public function modifyDefense(HeroCombatant $combatant, int $currentDefense): int
    {
        // Check if there are any living minions in the battle
        $livingMinions = $combatant->battle->combatants
            ->where('id', '!=', $combatant->id)
            ->where('hero_id', null) // Minions have no hero_id
            ->where('current_health', '>', 0)
            ->count();

        if ($livingMinions > 0) {
            return 999; // Nearly invulnerable while minions protect them
        }

        return $currentDefense;
    }
}
