<?php

namespace OpenDominion\Domain\HeroBattle\AI\Strategies;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Pirate Strategy
 *
 * Aggressive strategy with blade flurry emphasis.
 * Used by Rebel Corsair, Rebel Admiral.
 */
class PirateStrategy extends AbstractStrategy
{
    protected array $actionWeights = [
        'attack' => 2,
        'blade_flurry' => 3,
        'focus' => 1,
        'counter' => 1,
        'recover' => 1,
    ];

    public function suggestActions(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants,
        Collection $abilities
    ): Collection {
        $suggestions = collect();

        if ($this->isCriticalHealth($combatant)) {
            $suggestions->push(ActionSuggestion::critical('recover', null, 'Critical health'));
            return $suggestions;
        }

        $available = $this->getAvailableActions($combatant);
        $action = $this->weightedRandomSelection($available);
        $suggestions->push(ActionSuggestion::normal($action, null, 'Pirate strategy'));

        return $suggestions;
    }
}
