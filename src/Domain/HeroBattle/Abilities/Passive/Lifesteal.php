<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesDamage;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;

class Lifesteal extends AbstractAbility implements ModifiesDamage
{
    /**
     * Heals the attacker for a percentage of damage dealt
     *
     * IMPORTANT: Does NOT modify health directly - stores healing in context
     * Health changes are applied by HeroBattleService after ALL abilities process
     */
    public function afterDamageDealt(CombatContext $context): void
    {
        if ($context->damage > 0) {
            $healPercent = $this->config['heal_percent'] ?? 0.5;
            $healing = (int) round($context->damage * $healPercent);

            // Store healing in context - HeroBattleService will apply it
            $context->healing += $healing;

            $context->addMessage("{$context->attacker->name} heals for {$healing} health.");
        }
    }
}
