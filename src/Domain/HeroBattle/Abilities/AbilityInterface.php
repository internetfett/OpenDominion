<?php

namespace OpenDominion\Domain\HeroBattle\Abilities;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

interface AbilityInterface
{
    /**
     * Get the unique key for this ability
     */
    public function getKey(): string;

    /**
     * Get the display name
     */
    public function getName(): string;

    /**
     * Get the description
     */
    public function getDescription(): string;

    /**
     * Get the configuration for this ability instance
     */
    public function getConfig(): array;

    /**
     * Check if this ability has charges remaining
     */
    public function hasCharges(): bool;

    /**
     * Consume a charge of this ability
     */
    public function consume(): void;

    /**
     * Get remaining charges (null = unlimited)
     */
    public function getCharges(): ?int;

    /**
     * Set charges (for restoring saved state)
     */
    public function setCharges(?int $charges): void;

    /**
     * Check if ability is on cooldown
     */
    public function isOnCooldown(int $currentTurn): bool;

    /**
     * Mark ability as used on a specific turn
     */
    public function markUsed(int $currentTurn): void;

    /**
     * Get the last turn this ability was used
     */
    public function getLastUsedTurn(): ?int;

    /**
     * Set the last used turn (for restoring saved state)
     */
    public function setLastUsedTurn(?int $turn): void;

    /**
     * Get the cooldown in turns (null = no cooldown)
     */
    public function getCooldown(): ?int;

    /**
     * Get the current state for persistence
     */
    public function getState(): array;

    /**
     * Restore state from saved data
     */
    public function restoreState(array $state): void;
}
