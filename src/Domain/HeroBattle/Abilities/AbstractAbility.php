<?php

namespace OpenDominion\Domain\HeroBattle\Abilities;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;
use OpenDominion\Models\HeroCombatant;

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

    // ==================== Helper Methods for Ability Development ====================

    /**
     * Deal damage through the context (for step-based processing)
     */
    protected function dealDamage(int $amount, CombatContext $context): void
    {
        $context->damage = $amount;
    }

    /**
     * Add to existing damage (for bonus damage abilities)
     */
    protected function addBonusDamage(int $amount, CombatContext $context): void
    {
        $context->damage += $amount;
    }

    /**
     * Heal a combatant through the context
     */
    protected function healCombatant(HeroCombatant $combatant, int $amount, CombatContext $context): void
    {
        if ($combatant === $context->attacker) {
            $context->healing += $amount;
        } else {
            // Healing target - set negative damage
            $context->damage = -$amount;
        }
    }

    /**
     * Add a message to the combat log
     */
    protected function addCombatMessage(string $message, CombatContext $context): void
    {
        $context->addMessage($message);
    }

    /**
     * Format a message using a template and arguments
     */
    protected function formatMessage(string $template, ...$args): string
    {
        return sprintf($template, ...$args);
    }

    /**
     * Get a message template from ability config
     */
    protected function getMessageTemplate(string $key): ?string
    {
        return $this->config['messages'][$key] ?? null;
    }

    /**
     * Build and add a formatted message
     */
    protected function buildMessage(CombatContext $context, string $messageKey, ...$args): void
    {
        $template = $this->getMessageTemplate($messageKey);
        if ($template) {
            $message = sprintf($template, ...$args);
            $context->addMessage($message);
        }
    }
}
