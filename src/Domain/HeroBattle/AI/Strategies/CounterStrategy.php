<?php

namespace OpenDominion\Domain\HeroBattle\AI\Strategies;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Domain\HeroBattle\Combat\CombatCalculator;
use OpenDominion\Helpers\HeroHelper;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Counter Strategy
 *
 * Balanced strategy with emphasis on counter attacks.
 * Used by tactical NPCs like Nightbringer, Bandits, Gate Wardens.
 */
class CounterStrategy extends AbstractStrategy
{
    protected CombatCalculator $combatCalculator;

    protected array $actionWeights = [
        'attack' => 3,
        'defend' => 1,
        'focus' => 3,
        'counter' => 1,
        'recover' => 1,
    ];

    public function __construct(HeroHelper $heroHelper, CombatCalculator $combatCalculator)
    {
        parent::__construct($heroHelper);
        $this->combatCalculator = $combatCalculator;
    }

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
        $suggestions->push(ActionSuggestion::normal($action, null, 'Counter strategy'));

        return $suggestions;
    }
}
