<?php

namespace OpenDominion\HeroCombat\Analysis;

/**
 * Probability-weighted result of one previewed turn, reduced to what the value function
 * needs. Health and offense are keyed by combatant id.
 */
final readonly class TurnOutcome
{
    /**
     * @param array<int, float> $health expected health after the turn
     * @param array<int, float> $offense expected attack potential after the turn
     * @param array<int, float> $deathChance probability each combatant is down after the turn
     */
    public function __construct(
        public array $health,
        public array $offense,
        public array $deathChance,
    ) {
    }
}
