<?php

namespace OpenDominion\Domain\HeroBattle\Context;

use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * CombatContext - Stores combat state for phase-based processing
 *
 * IMPORTANT: For simultaneous turn processing, abilities modify this context
 * but do NOT directly modify combatant health/stats. The HeroBattleService
 * applies all changes after ALL abilities have processed.
 *
 * Processing Phases:
 * 1. Calculate base damage/effects for all combatants
 * 2. Trigger abilities - they modify context values
 * 3. Apply ALL damage/healing simultaneously
 * 4. Process death triggers
 * 5. Process status effects
 */
class CombatContext
{
    // Combatants involved
    public HeroCombatant $attacker;
    public HeroCombatant $target;
    public HeroBattle $battle;
    public array $actionDef;

    // Damage/healing to apply (modified by abilities)
    public int $damage = 0;
    public int $healing = 0;

    // Combat flags
    public bool $evaded = false;
    public bool $countered = false;
    public float $evadeMultiplier = 1.0;

    // Messages for battle log
    public array $messages = [];

    public function __construct(
        HeroCombatant $attacker,
        HeroCombatant $target,
        HeroBattle $battle,
        array $actionDef = []
    ) {
        $this->attacker = $attacker;
        $this->target = $target;
        $this->battle = $battle;
        $this->actionDef = $actionDef;
    }

    /**
     * Add a message to the combat log
     */
    public function addMessage(string $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * Check if the attacker has focus
     */
    public function hasFocus(): bool
    {
        return $this->attacker->has_focus ?? false;
    }

    /**
     * Check if the target is defending
     */
    public function isDefending(): bool
    {
        return ($this->target->action ?? '') === 'defend';
    }

    /**
     * Check if the damage would be lethal
     */
    public function willBeLethal(): bool
    {
        return ($this->target->current_health - $this->damage) <= 0;
    }

    /**
     * Get all messages as a single string
     */
    public function getMessagesString(): string
    {
        return implode(' ', $this->messages);
    }

    /**
     * Apply damage to target (called by HeroBattleService after all abilities process)
     */
    public function applyDamage(): void
    {
        if ($this->damage > 0) {
            // Apply to shield first, then health
            if ($this->target->shield > 0) {
                $shieldDamage = min($this->damage, $this->target->shield);
                $this->target->shield -= $shieldDamage;
                $remainingDamage = $this->damage - $shieldDamage;
                if ($remainingDamage > 0) {
                    $this->target->current_health -= $remainingDamage;
                }
            } else {
                $this->target->current_health -= $this->damage;
            }
        }
    }

    /**
     * Apply healing to attacker (called by HeroBattleService after all abilities process)
     */
    public function applyHealing(): void
    {
        if ($this->healing > 0) {
            $this->attacker->current_health = min(
                $this->attacker->current_health + $this->healing,
                $this->attacker->max_health
            );
        }
    }
}
