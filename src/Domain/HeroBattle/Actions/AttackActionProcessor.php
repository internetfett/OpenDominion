<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Attack Action Processor
 *
 * Calculates damage and handles combat mechanics like evasion and counters.
 * Does NOT directly modify health - stores values in context for step-based turn processing.
 */
class AttackActionProcessor extends AbstractActionProcessor
{
    public function process(CombatContext $context): void
    {
        $this->calculateDamage($context);
        $this->handleEvasion($context);
        $this->handleCounter($context);
        $this->buildCombatMessage($context);
        $this->afterCombat($context);
    }

    /**
     * Calculate base damage with ability modifiers
     * Override this to customize damage calculation
     */
    protected function calculateDamage(CombatContext $context): void
    {
        // Ensure abilities are loaded
        $attackerAbilities = $context->attackerAbilities ?? collect();
        $targetAbilities = $context->targetAbilities ?? collect();

        // Calculate base damage with ability modifiers
        $context->damage = $this->combatCalculator->calculateCombatDamage(
            $context->attacker,
            $context->target,
            $context->action,
            $context->targetAction,
            $context->actionDef,
            $attackerAbilities,
            $targetAbilities
        );
    }

    /**
     * Handle evasion mechanics
     * Override this to customize evasion behavior
     */
    protected function handleEvasion(CombatContext $context): void
    {
        $targetAbilities = $context->targetAbilities ?? collect();

        // Calculate evasion with ability modifiers
        $context->evaded = $this->combatCalculator->calculateCombatEvade(
            $context->target,
            $context->actionDef,
            $targetAbilities
        );

        // Apply evasion multiplier
        if ($context->evaded && $context->damage > 0) {
            $context->damageBeforeEvasion = $context->damage;
            $context->damage = (int) round($context->damage * $context->evadeMultiplier);
        }
    }

    /**
     * Handle counter attack mechanics
     * Override this to customize counter behavior
     */
    protected function handleCounter(CombatContext $context): void
    {
        $attackerAbilities = $context->attackerAbilities ?? collect();
        $targetAbilities = $context->targetAbilities ?? collect();

        // Check for counter attack
        if ($context->targetAction === 'counter') {
            $context->countered = true;
            $counterDamage = $this->combatCalculator->calculateCombatDamage(
                $context->target,
                $context->attacker,
                $context->targetAction,
                $context->action,
                $context->actionDef,
                $targetAbilities,
                $attackerAbilities
            );
            // Counter damage is applied to attacker
            $context->healing = -$counterDamage;
            $context->counterDamage = $counterDamage;
        }
    }

    /**
     * Build combat message based on what happened
     * Override this to customize messages
     */
    protected function buildCombatMessage(CombatContext $context): void
    {
        $this->buildMessage(
            $context,
            $context->damageBeforeEvasion ?? 0,
            $context->counterDamage ?? 0
        );
    }

    /**
     * After combat processing (focus spending, etc.)
     * Override this to add custom after-effects
     */
    protected function afterCombat(CombatContext $context): void
    {
        // Spend focus if used
        $this->spendFocus($context->attacker);
    }

    protected function buildMessage(CombatContext $context, int $damageBeforeEvasion, int $counterDamage): void
    {
        if ($context->evaded && $context->damage > 0) {
            if ($context->countered) {
                $message = $this->getMessage(
                    $context,
                    'evaded_countered',
                    $context->attacker->name,
                    $damageBeforeEvasion,
                    $context->target->name,
                    $context->damage,
                    $context->target->name,
                    $counterDamage
                );
            } else {
                $message = $this->getMessage(
                    $context,
                    'evaded',
                    $context->attacker->name,
                    $damageBeforeEvasion,
                    $context->target->name,
                    $context->damage
                );
            }
        } else {
            if ($context->countered) {
                $message = $this->getMessage(
                    $context,
                    'countered',
                    $context->attacker->name,
                    $context->damage,
                    $context->target->name,
                    $counterDamage
                );
            } else {
                $message = $this->getMessage(
                    $context,
                    'hit',
                    $context->attacker->name,
                    $context->damage,
                    $context->target->name
                );
            }
        }

        $context->addMessage($message);
    }

    protected function spendFocus($combatant): void
    {
        // Note: Channeling ability will override this behavior
        $combatant->has_focus = false;
    }
}
