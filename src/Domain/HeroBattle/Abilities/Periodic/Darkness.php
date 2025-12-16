<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Periodic;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\InfluencesDecisions;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Darkness Ability
 *
 * Periodic ability that automatically triggers every N turns to boost evasion.
 * Forces the combatant to use the 'darkness' action on trigger turns.
 *
 * Used by: The Nightbringer
 */
class Darkness extends AbstractAbility implements InfluencesDecisions
{
    /**
     * Suggest actions - force darkness action on periodic turns
     */
    public function suggestActions(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants
    ): Collection {
        $suggestions = collect();

        // Only trigger if evasion is not already maxed
        if ($combatant->evasion >= 100) {
            return $suggestions;
        }

        $turnPeriod = $this->config['attributes']['turns'] ?? 2;

        // Check if this is a trigger turn (turn 1, 3, 5, etc. for period of 2)
        if ((($battle->current_turn - 1) % $turnPeriod) == 0) {
            $suggestions->push(
                ActionSuggestion::forced('darkness', null, 'Darkness period trigger')
            );
        }

        return $suggestions;
    }
}
