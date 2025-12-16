<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Periodic;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\InfluencesDecisions;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Summon Skeleton Ability
 *
 * Periodic ability that automatically summons a skeleton warrior every N turns.
 * Forces the combatant to use the 'summon_skeleton' action on trigger turns.
 *
 * Used by: The Eternal Guardian
 */
class SummonSkeleton extends AbstractAbility implements InfluencesDecisions
{
    /**
     * Suggest actions - force summon action on periodic turns
     */
    public function suggestActions(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants
    ): Collection {
        $suggestions = collect();

        $turnPeriod = $this->config['attributes']['turns'] ?? 4;

        // Check if this is a trigger turn (turn 1, 5, 9, etc. for period of 4)
        if ((($battle->current_turn - 1) % $turnPeriod) == 0) {
            $suggestions->push(
                ActionSuggestion::forced('summon_skeleton', null, 'Summon period trigger')
            );
        }

        return $suggestions;
    }
}
