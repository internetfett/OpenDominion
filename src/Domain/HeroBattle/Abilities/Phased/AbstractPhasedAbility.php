<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Phased;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\PhaseCycles;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

abstract class AbstractPhasedAbility extends AbstractAbility implements PhaseCycles
{
    /**
     * Process phase changes and apply phase-specific abilities.
     */
    public function processPhase(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants
    ): string {
        // Only process if combatant is alive
        if ($combatant->current_health <= 0) {
            return '';
        }

        $currentPhase = $this->getCurrentPhase($battle->current_turn);
        $status = $combatant->status ?? [];
        $abilityKey = $this->getAbilityKey();
        $lastPhase = $status["{$abilityKey}_phase"] ?? null;

        // Only apply phase changes if phase has changed
        if ($currentPhase === $lastPhase) {
            return '';
        }

        $status["{$abilityKey}_phase"] = $currentPhase;
        $combatant->update(['status' => $status]);

        $phaseDef = $this->getPhaseDefinition($currentPhase);
        if (!$phaseDef) {
            return '';
        }

        return $this->applyPhaseEffects($combatant, $battle, $phaseDef);
    }

    /**
     * Get the current phase number based on turn.
     */
    public function getCurrentPhase(int $currentTurn): int
    {
        $turnsPerPhase = $this->config['attributes']['turns_per_phase'] ?? 4;
        $maxPhase = $this->config['attributes']['max_phase'] ?? 5;
        $cyclePhases = $this->config['attributes']['cycle_phases'] ?? false;

        if ($cyclePhases) {
            // Cycle back to phase 1 after reaching max phase
            return (int) ((floor(($currentTurn - 1) / $turnsPerPhase) % $maxPhase) + 1);
        }

        // Stay at max phase after reaching it
        return (int) min($maxPhase, floor(($currentTurn - 1) / $turnsPerPhase) + 1);
    }

    /**
     * Get the phase definition for a specific phase number.
     */
    public function getPhaseDefinition(int $phaseNumber): ?array
    {
        return $this->config['attributes']['phases'][$phaseNumber] ?? null;
    }

    /**
     * Apply the effects of a phase.
     * Can be overridden by subclasses for custom behavior.
     */
    protected function applyPhaseEffects(
        HeroCombatant $combatant,
        HeroBattle $battle,
        array $phaseDef
    ): string {
        $message = '';

        // Update combatant's own abilities
        if (isset($phaseDef['self_abilities'])) {
            $this->applySelfAbilities($combatant, $phaseDef['self_abilities']);

            if (isset($phaseDef['message'])) {
                $message = sprintf($phaseDef['message'], $combatant->name);
            }
        }

        // Update allied NPC abilities
        if (isset($phaseDef['ally_abilities'])) {
            $this->applyAllyAbilities($combatant, $battle, $phaseDef['ally_abilities']);
        }

        return $message;
    }

    /**
     * Apply abilities to the combatant themselves.
     */
    protected function applySelfAbilities(HeroCombatant $combatant, array $selfAbilities): void
    {
        $status = $combatant->status ?? [];

        // Store base abilities on first phase change
        if (!isset($status['base_abilities'])) {
            $status['base_abilities'] = $combatant->abilities ?? [];
            $combatant->status = $status;
        }

        // Merge base abilities with phase abilities
        $combatant->abilities = array_unique(array_merge(
            $status['base_abilities'],
            $selfAbilities
        ));
        $combatant->save();
    }

    /**
     * Apply abilities to allied NPCs.
     */
    protected function applyAllyAbilities(
        HeroCombatant $combatant,
        HeroBattle $battle,
        array $allyAbilities
    ): void {
        $allies = $battle->combatants
            ->where('id', '!=', $combatant->id)
            ->where('hero_id', null)
            ->where('current_health', '>', 0);

        foreach ($allies as $ally) {
            $allyStatus = $ally->status ?? [];
            if (!isset($allyStatus['base_abilities'])) {
                $allyStatus['base_abilities'] = $ally->abilities ?? [];
            }

            $ally->abilities = array_unique(array_merge(
                $allyStatus['base_abilities'],
                $allyAbilities
            ));
            $ally->status = $allyStatus;
            $ally->save();
        }
    }

    /**
     * Get the ability key for status tracking.
     * Should be overridden by subclasses to return their specific key.
     */
    abstract protected function getAbilityKey(): string;
}
