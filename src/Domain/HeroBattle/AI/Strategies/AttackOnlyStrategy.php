<?php

namespace OpenDominion\Domain\HeroBattle\AI\Strategies;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Attack Only Strategy
 *
 * Mindless attacker - only uses attack action.
 * Used by simple NPCs like skeleton warriors.
 */
class AttackOnlyStrategy extends AbstractStrategy
{
    protected array $actionWeights = [
        'attack' => 1,
    ];

    public function suggestActions(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants,
        Collection $abilities
    ): Collection {
        $suggestions = collect();
        $suggestions->push(ActionSuggestion::normal('attack', null, 'Attack only strategy'));
        return $suggestions;
    }
}
