<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Active;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesDamage;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;

class PowerStrike extends AbstractAbility implements ModifiesDamage
{
    /**
     * Deals bonus damage on attack
     * Has a cooldown - can only be used every N turns
     *
     * IMPORTANT: Does NOT modify health directly - modifies damage in context
     * Damage is applied by HeroBattleService after ALL abilities process
     */
    public function afterDamageDealt(CombatContext $context): void
    {
        // Check if ability is on cooldown
        if ($this->isOnCooldown($context->battle->current_turn)) {
            return;
        }

        // Check if this is the turn to use it
        // (In real implementation, this would be decided by AI or player action)
        $shouldUse = $context->attacker->action === 'power_strike';

        if ($shouldUse && $context->damage > 0) {
            $bonusDamage = $this->config['bonus_damage'] ?? 20;

            // Add to damage in context - HeroBattleService will apply it
            $context->damage += $bonusDamage;

            // Mark as used
            $this->markUsed($context->battle->current_turn);

            $context->addMessage("{$context->attacker->name} uses Power Strike for {$bonusDamage} bonus damage!");
        }
    }
}
