<?php

namespace OpenDominion\Domain\HeroBattle\AI\Strategies;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Defensive Strategy
 *
 * Balanced approach with defensive stance.
 * No focus usage - prefers defend and counter.
 */
class DefensiveStrategy extends AbstractStrategy
{
    protected array $actionWeights = [
        'attack' => 3,
        'defend' => 1,
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
        $suggestions->push(ActionSuggestion::normal($action, null, 'Defensive strategy'));

        return $suggestions;
    }
}
