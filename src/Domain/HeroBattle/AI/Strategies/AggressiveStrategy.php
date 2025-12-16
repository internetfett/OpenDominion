<?php

namespace OpenDominion\Domain\HeroBattle\AI\Strategies;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Aggressive Strategy
 *
 * Emphasizes offense with frequent focus usage for maximum damage.
 * Minimal defensive actions.
 */
class AggressiveStrategy extends AbstractStrategy
{
    protected array $actionWeights = [
        'attack' => 5,
        'focus' => 3,
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

        // Even aggressive strategy recovers at critical health
        if ($this->isCriticalHealth($combatant)) {
            $suggestions->push(ActionSuggestion::critical('recover', null, 'Critical health'));
            return $suggestions;
        }

        $available = $this->getAvailableActions($combatant);
        $action = $this->weightedRandomSelection($available);
        $suggestions->push(ActionSuggestion::normal($action, null, 'Aggressive strategy'));

        return $suggestions;
    }
}
