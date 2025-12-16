<?php

namespace OpenDominion\Domain\HeroBattle\AI;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\InfluencesDecisions;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Combat AI Orchestrator
 *
 * Coordinates decision-making between strategy and abilities.
 * Resolves conflicts using priority system and enforces constraints.
 */
class CombatAI
{
    protected StrategyInterface $strategy;
    protected Collection $abilities;
    protected Collection $limitedActions;

    public function __construct(
        StrategyInterface $strategy,
        Collection $abilities,
        Collection $limitedActions
    ) {
        $this->strategy = $strategy;
        $this->abilities = $abilities;
        $this->limitedActions = $limitedActions;
    }

    /**
     * Determine the best action for a combatant
     *
     * @param HeroCombatant $combatant
     * @param HeroBattle $battle
     * @param Collection $livingCombatants
     * @return array ['action' => string, 'target' => int|null]
     */
    public function determineAction(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants
    ): array {
        // Collect all suggestions
        $suggestions = $this->collectSuggestions($combatant, $battle, $livingCombatants);

        // Filter out vetoed actions
        $vetoes = $suggestions->where('isVeto', true);
        $vetoedActions = $vetoes->pluck('action')->unique();

        $validSuggestions = $suggestions
            ->where('isVeto', false)
            ->reject(fn($s) => $vetoedActions->contains($s->action));

        // Apply constraints (limited actions can't repeat)
        $constrained = $this->applyConstraints($validSuggestions, $combatant);

        // Select best suggestion by priority
        $selected = $this->selectBestSuggestion($constrained, $combatant);

        // Apply action transformations (e.g., attack → crushing_blow)
        $final = $this->applyActionTransformations($selected, $combatant);

        return $final->toArray();
    }

    /**
     * Collect suggestions from abilities and strategy
     */
    protected function collectSuggestions(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants
    ): Collection {
        $suggestions = collect();

        // Collect from abilities that influence decisions
        foreach ($this->abilities as $ability) {
            if ($ability instanceof InfluencesDecisions) {
                $abilitySuggestions = $ability->suggestActions($combatant, $battle, $livingCombatants);
                $suggestions = $suggestions->merge($abilitySuggestions);
            }
        }

        // Collect from strategy
        $strategySuggestions = $this->strategy->suggestActions(
            $combatant,
            $battle,
            $livingCombatants,
            $this->abilities
        );
        $suggestions = $suggestions->merge($strategySuggestions);

        return $suggestions;
    }

    /**
     * Apply constraints (limited actions can't be used consecutively)
     */
    protected function applyConstraints(Collection $suggestions, HeroCombatant $combatant): Collection
    {
        $lastAction = $combatant->last_action;

        return $suggestions->reject(function($suggestion) use ($lastAction) {
            // Filter out limited actions that were used last turn
            return $this->limitedActions->contains($suggestion->action) &&
                   $suggestion->action === $lastAction;
        });
    }

    /**
     * Select the best suggestion from available options
     */
    protected function selectBestSuggestion(Collection $suggestions, HeroCombatant $combatant): ActionSuggestion
    {
        if ($suggestions->isEmpty()) {
            // Fallback: attack if no suggestions
            return ActionSuggestion::fallback('attack', null, 'No valid suggestions');
        }

        // Sort by priority (highest first)
        $sorted = $suggestions->sortByDesc('priority');

        // Return highest priority suggestion
        return $sorted->first();
    }

    /**
     * Apply action transformations based on abilities
     * Example: attack → crushing_blow if combatant has crushing_blow ability
     */
    protected function applyActionTransformations(
        ActionSuggestion $suggestion,
        HeroCombatant $combatant
    ): ActionSuggestion {
        // Check for crushing_blow transformation
        if ($suggestion->action === 'attack' && in_array('crushing_blow', $combatant->abilities ?? [])) {
            return new ActionSuggestion(
                'crushing_blow',
                $suggestion->targetId,
                $suggestion->priority,
                'Attack upgraded to crushing_blow',
                false
            );
        }

        return $suggestion;
    }
}
