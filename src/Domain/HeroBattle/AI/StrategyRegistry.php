<?php

namespace OpenDominion\Domain\HeroBattle\AI;

use OpenDominion\Calculators\Dominion\HeroCalculator;
use OpenDominion\Domain\HeroBattle\AI\Strategies\AggressiveStrategy;
use OpenDominion\Domain\HeroBattle\AI\Strategies\AttackOnlyStrategy;
use OpenDominion\Domain\HeroBattle\AI\Strategies\BalancedStrategy;
use OpenDominion\Domain\HeroBattle\AI\Strategies\CounterStrategy;
use OpenDominion\Domain\HeroBattle\AI\Strategies\DefensiveStrategy;
use OpenDominion\Domain\HeroBattle\AI\Strategies\PirateStrategy;
use OpenDominion\Domain\HeroBattle\AI\Strategies\SummonerStrategy;
use OpenDominion\Domain\HeroBattle\Combat\CombatCalculator;
use OpenDominion\Helpers\HeroHelper;

/**
 * Strategy Registry
 *
 * Central registry for all combat AI strategies.
 * Handles dependency injection and strategy instantiation.
 * Follows the registry pattern similar to AbilityRegistry.
 */
class StrategyRegistry
{
    protected array $strategies = [];

    public function __construct(
        HeroHelper $heroHelper,
        CombatCalculator $combatCalculator,
        HeroCalculator $heroCalculator
    ) {
        // Register all strategies with injected dependencies
        $this->register('balanced', new BalancedStrategy($heroHelper));
        $this->register('aggressive', new AggressiveStrategy($heroHelper));
        $this->register('defensive', new DefensiveStrategy($heroHelper));
        $this->register('attack', new AttackOnlyStrategy($heroHelper));
        $this->register('counter', new CounterStrategy($heroHelper, $combatCalculator));
        $this->register('pirate', new PirateStrategy($heroHelper));
        $this->register('summoner', new SummonerStrategy($heroHelper));
    }

    /**
     * Get a strategy by key
     *
     * @param string $key Strategy key (e.g., 'balanced', 'aggressive')
     * @return StrategyInterface The strategy, or balanced as fallback
     */
    public function get(string $key): StrategyInterface
    {
        return $this->strategies[$key] ?? $this->strategies['balanced'];
    }

    /**
     * Register a strategy
     *
     * @param string $key Strategy key
     * @param StrategyInterface $strategy Strategy instance
     */
    protected function register(string $key, StrategyInterface $strategy): void
    {
        $this->strategies[$key] = $strategy;
    }

    /**
     * Get all registered strategy keys
     *
     * @return array
     */
    public function getAvailableStrategies(): array
    {
        return array_keys($this->strategies);
    }
}
