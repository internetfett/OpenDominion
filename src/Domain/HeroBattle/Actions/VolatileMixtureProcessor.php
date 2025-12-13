<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Volatile Mixture Action Processor
 *
 * Alchemist special ability - hurls an unstable concoction that can backfire.
 * Has a success chance (default 80%) and deals bonus damage on success.
 * On failure, damages the attacker instead.
 */
class VolatileMixtureProcessor extends AttackActionProcessor
{
    /**
     * Override damage calculation to include backfire mechanic
     */
    protected function calculateDamage(CombatContext $context): void
    {
        // Get success chance from action definition
        $successChance = $context->actionDef['attributes']['success_chance'] ?? 0.8;
        $attackBonus = $context->actionDef['attributes']['attack_bonus'] ?? 1.5;

        // Calculate base damage
        parent::calculateDamage($context);

        // Roll for success/backfire
        $roll = mt_rand(1, 100) / 100;

        if ($roll <= $successChance) {
            // SUCCESS - Apply attack bonus
            $context->damage = (int) round($context->damage * $attackBonus);
        } else {
            // BACKFIRE - Damage redirects to attacker
            $context->backfired = true;

            // Swap damage to healing (negative healing = damage to attacker)
            $context->healing = -$context->damage;
            $context->damage = 0;  // No damage to target
        }
    }

    /**
     * Override message building to handle backfire scenarios
     */
    protected function buildCombatMessage(CombatContext $context): void
    {
        if ($context->backfired) {
            // Backfire messages
            if ($context->countered) {
                $message = $this->getMessage(
                    $context,
                    'backfire_countered',
                    $context->attacker->name,
                    $context->attacker->name,
                    abs($context->healing),
                    $context->target->name,
                    $context->counterDamage
                );
            } else {
                $message = $this->getMessage(
                    $context,
                    'backfire',
                    $context->attacker->name,
                    $context->attacker->name,
                    abs($context->healing)
                );
            }
        } else {
            // Success messages (with evade/counter variants)
            if ($context->evaded && $context->damage > 0) {
                if ($context->countered) {
                    $message = $this->getMessage(
                        $context,
                        'success_evaded_countered',
                        $context->attacker->name,
                        $context->target->name,
                        $context->damage,
                        $context->target->name,
                        $context->counterDamage
                    );
                } else {
                    $message = $this->getMessage(
                        $context,
                        'success_evaded',
                        $context->attacker->name,
                        $context->target->name,
                        $context->damage
                    );
                }
            } else {
                if ($context->countered) {
                    $message = $this->getMessage(
                        $context,
                        'success_countered',
                        $context->attacker->name,
                        $context->damage,
                        $context->target->name,
                        $context->counterDamage
                    );
                } else {
                    $message = $this->getMessage(
                        $context,
                        'success',
                        $context->attacker->name,
                        $context->damage,
                        $context->target->name
                    );
                }
            }
        }

        $context->addMessage($message);
    }
}
