<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use Illuminate\Support\Collection;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Influences Decisions Trait
 *
 * Interface for abilities that can influence AI decision-making.
 * Abilities implementing this trait can:
 * - Force specific actions (darkness, summon_skeleton)
 * - Suggest preferred actions
 * - Veto actions they don't want the combatant to take
 *
 * Examples:
 * - Darkness: Forces 'darkness' action every 2 turns
 * - Summon Skeleton: Forces 'summon_skeleton' action every 4 turns
 * - Future abilities could veto certain actions or suggest combos
 */
interface InfluencesDecisions
{
    /**
     * Suggest actions this ability wants to take
     *
     * @param HeroCombatant $combatant The combatant with this ability
     * @param HeroBattle $battle The battle context
     * @param Collection $livingCombatants All living combatants
     * @return Collection Collection of ActionSuggestion objects
     */
    public function suggestActions(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants
    ): Collection;
}
