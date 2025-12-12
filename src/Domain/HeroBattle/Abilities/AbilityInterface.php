<?php

namespace OpenDominion\Domain\HeroBattle\Abilities;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

interface AbilityInterface
{
    /**
     * Check if this ability has charges remaining
     */
    public function hasCharges(): bool;

    /**
     * Consume a charge of this ability
     */
    public function consume(): void;

    /**
     * Check if ability is on cooldown
     */
    public function isOnCooldown(int $currentTurn): bool;

    /**
     * Mark ability as used on a specific turn
     */
    public function markUsed(int $currentTurn): void;

    /**
     * Get the current state for persistence
     */
    public function getState(): array;

    /**
     * Restore state from saved data
     */
    public function restoreState(array $state): void;
}
