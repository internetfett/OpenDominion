<?php

namespace OpenDominion\Domain\HeroBattle\AI\Strategies;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Balanced Strategy
 *
 * Default strategy with equal mix of offensive and defensive actions.
 * Emphasizes emergency recovery at low health.
 */
class BalancedStrategy extends AbstractStrategy
{
    protected array $actionWeights = [
        'attack' => 4,
        'defend' => 1,
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

        // Emergency recovery at low health
        if ($this->isCriticalHealth($combatant)) {
            $suggestions->push(ActionSuggestion::critical('recover', null, 'Critical health - emergency recovery'));
            return $suggestions;
        }

        // Normal weighted selection
        $available = $this->getAvailableActions($combatant);

        // Boost recover weight when at low health
        if ($this->isLowHealth($combatant) && $available->has('recover')) {
            $available['recover'] = $available->get('attack', 4) * 2;
        }

        $action = $this->weightedRandomSelection($available);
        $suggestions->push(ActionSuggestion::normal($action, null, 'Balanced strategy selection'));

        return $suggestions;
    }
}
