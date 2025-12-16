<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use Illuminate\Support\Collection;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Interface for abilities that cycle through phases.
 * Each phase can grant different abilities or effects.
 */
interface PhaseCycles
{
    /**
     * Process phase changes and apply phase-specific abilities.
     *
     * @param HeroCombatant $combatant The combatant with this ability
     * @param HeroBattle $battle The current battle
     * @param Collection $livingCombatants All living combatants
     * @return string Status message describing the phase change
     */
    public function processPhase(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants
    ): string;

    /**
     * Get the current phase number based on the current turn.
     *
     * @param int $currentTurn Current battle turn
     * @return int The current phase number (1-indexed)
     */
    public function getCurrentPhase(int $currentTurn): int;

    /**
     * Get the phase definition for a specific phase number.
     *
     * @param int $phaseNumber The phase number
     * @return array|null Phase definition or null if not found
     */
    public function getPhaseDefinition(int $phaseNumber): ?array;
}
