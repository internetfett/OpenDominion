<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Attack Action Processor
 *
 * Calculates damage and handles combat mechanics like evasion and counters.
 * Does NOT directly modify health - stores values in context for phase-based processing.
 */
class AttackActionProcessor extends AbstractActionProcessor
{
    public function process(CombatContext $context): void
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

        // Calculate evasion with ability modifiers
        $context->evaded = $this->combatCalculator->calculateCombatEvade(
            $context->target,
            $context->actionDef,
            $targetAbilities
        );

        // Apply evasion multiplier
        if ($context->evaded && $context->damage > 0) {
            $damageBeforeEvasion = $context->damage;
            $context->damage = (int) round($context->damage * $context->evadeMultiplier);
        }

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
        }

        // Build message
        $this->buildMessage($context, $damageBeforeEvasion ?? 0, $counterDamage ?? 0);

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
