<?php

namespace OpenDominion\Domain\HeroBattle\Abilities;

abstract class AbstractAbility implements AbilityInterface
{
    public string $key;
    public string $name;
    public string $description;
    public array $config;
    public ?int $charges;
    public ?int $cooldown;
    public ?int $lastUsedTurn;

    public function __construct(string $key, array $config = [])
    {
        $this->key = $key;
        $this->config = $config;
        $this->name = $config['display_name'] ?? ucfirst($key);
        $this->description = $config['description'] ?? '';
        $this->charges = $config['charges'] ?? null;
        $this->cooldown = $config['cooldown'] ?? null;
        $this->lastUsedTurn = null;
    }

    public function hasCharges(): bool
    {
        return $this->charges === null || $this->charges > 0;
    }

    public function consume(): void
    {
        if ($this->charges !== null && $this->charges > 0) {
            $this->charges--;
        }
    }

    public function isOnCooldown(int $currentTurn): bool
    {
        if ($this->cooldown === null || $this->lastUsedTurn === null) {
            return false;
        }

        $turnsSinceUse = $currentTurn - $this->lastUsedTurn;
        return $turnsSinceUse < $this->cooldown;
    }

    public function markUsed(int $currentTurn): void
    {
        $this->lastUsedTurn = $currentTurn;
    }

    public function getState(): array
    {
        return array_filter([
            'charges' => $this->charges,
            'last_used_turn' => $this->lastUsedTurn,
        ], fn($v) => $v !== null);
    }

    public function restoreState(array $state): void
    {
        if (isset($state['charges'])) {
            $this->charges = $state['charges'];
        }
        if (isset($state['last_used_turn'])) {
            $this->lastUsedTurn = $state['last_used_turn'];
        }
    }
}
