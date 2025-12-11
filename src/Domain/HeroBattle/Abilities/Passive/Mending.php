<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesHealing;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Mending Ability
 *
 * Enhances recover action when focused, consuming the focus
 */
class Mending extends AbstractAbility implements ModifiesHealing
{
    /**
     * Enhance healing when focused
     */
    public function afterHealingCalculated(CombatContext $context): void
    {
        if ($context->attacker->has_focus) {
            // Double healing when focused
            $bonusHealing = $context->healing;
            $context->healing += $bonusHealing;

            // Spend focus
            $context->attacker->has_focus = false;

            $context->addMessage("{$context->attacker->name} uses focused mending for extra healing!");
        }
    }
}
