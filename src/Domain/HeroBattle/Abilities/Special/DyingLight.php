<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Special;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\TriggersOnDeath;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

class DyingLight extends AbstractAbility implements TriggersOnDeath
{
    /**
     * Triggered when this combatant dies.
     * Explodes in a blast of light, exposing the Nightbringer by reducing evasion to 0.
     */
    public function onDeath(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants
    ): string {
        // Find the Nightbringer in this battle
        $nightbringer = $battle->combatants
            ->where('name', 'The Nightbringer')
            ->first();

        if ($nightbringer) {
            // Reduce Nightbringer's evasion to 0
            $nightbringer->evasion = 0;
            $nightbringer->save();

            return "{$combatant->name} explodes in a blast of light, exposing the Nightbringer!";
        }

        return "{$combatant->name} explodes in a blast of light.";
    }
}
