<?php

namespace OpenDominion\Domain\HeroBattle\AI\Strategies;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Summoner Strategy
 *
 * Defensive strategy with no attacks - relies on summons to do damage.
 * Used by The Eternal Guardian.
 */
class SummonerStrategy extends AbstractStrategy
{
    protected array $actionWeights = [
        'defend' => 4,
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
        $suggestions->push(ActionSuggestion::normal($action, null, 'Summoner strategy'));

        return $suggestions;
    }
}
