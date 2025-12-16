<?php

namespace OpenDominion\Domain\HeroBattle\AI\Strategies;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\AI\StrategyInterface;
use OpenDominion\Helpers\HeroHelper;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Abstract Strategy
 *
 * Base class for all combat AI strategies.
 * Provides common functionality for action selection and filtering.
 */
abstract class AbstractStrategy implements StrategyInterface
{
    protected HeroHelper $heroHelper;
    protected array $actionWeights = [];

    public function __construct(HeroHelper $heroHelper)
    {
        $this->heroHelper = $heroHelper;
    }

    /**
     * Get available actions for a combatant
     * Removes actions that are currently unavailable
     */
    protected function getAvailableActions(HeroCombatant $combatant): Collection
    {
        $options = collect($this->actionWeights);

        // Remove focus if already active
        if ($combatant->has_focus) {
            $options->forget('focus');
        }

        // Remove recover if already at max health
        if ($combatant->health <= ($combatant->current_health + $combatant->recover)) {
            $options->forget('recover');
        }

        return $options;
    }

    /**
     * Weighted random selection from options
     * Migrated from HeroBattleService::randomAction()
     *
     * @param Collection $options Action weights ['action' => weight]
     * @return string Selected action
     */
    protected function weightedRandomSelection(Collection $options): string
    {
        if ($options->isEmpty()) {
            return 'attack'; // Fallback
        }

        return random_choice_weighted($options->toArray());
    }

    /**
     * Find opponent combatants (living enemies)
     */
    protected function findOpponents(HeroCombatant $combatant, Collection $allCombatants): Collection
    {
        // For now, all other combatants are opponents (no team support yet)
        return $allCombatants->where('id', '!=', $combatant->id);
    }

    /**
     * Find ally combatants (for future team support)
     */
    protected function findAllies(HeroCombatant $combatant, Collection $allCombatants): Collection
    {
        // For now, no allies (future: filter by team_id)
        return collect();
    }

    /**
     * Check if combatant is in critical health
     */
    protected function isCriticalHealth(HeroCombatant $combatant): bool
    {
        return $combatant->current_health <= 40;
    }

    /**
     * Check if combatant is at low health
     */
    protected function isLowHealth(HeroCombatant $combatant): bool
    {
        $healthPercent = $combatant->current_health / $combatant->health;
        return $healthPercent <= 0.5;
    }

    /**
     * Get the name of this strategy
     */
    public function getName(): string
    {
        return class_basename(static::class);
    }
}
