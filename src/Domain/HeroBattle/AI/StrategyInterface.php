<?php

namespace OpenDominion\Domain\HeroBattle\AI;

use Illuminate\Support\Collection;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Strategy Interface
 *
 * Contract for all combat AI strategies.
 * Strategies analyze the battle state and suggest actions based on their behavioral profile.
 */
interface StrategyInterface
{
    /**
     * Suggest actions based on this strategy
     *
     * @param HeroCombatant $combatant The combatant making the decision
     * @param HeroBattle $battle The battle context
     * @param Collection $livingCombatants All living combatants in the battle
     * @param Collection $abilities Combatant's abilities (for context-aware decisions)
     * @return Collection Collection of ActionSuggestion objects
     */
    public function suggestActions(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants,
        Collection $abilities
    ): Collection;

    /**
     * Get the name of this strategy
     *
     * @return string Strategy name (e.g., "balanced", "aggressive")
     */
    public function getName(): string;
}
