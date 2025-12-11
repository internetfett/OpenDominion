<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesFocus;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Channeling Ability
 *
 * - Prevents focus from being consumed on attacks
 * - Allows stacking focus bonus when using focus action while already focused
 */
class Channeling extends AbstractAbility implements ModifiesFocus
{
    /**
     * When focus action is used while already focused, add more focus
     */
    public function afterFocus(CombatContext $context): void
    {
        if ($context->attacker->has_focus && $context->attacker->hero !== null) {
            $combatStats = app('OpenDominion\Calculators\Dominion\HeroCalculator')
                ->getHeroCombatStats($context->attacker->hero);

            $context->attacker->focus += $combatStats['focus'];

            $context->addMessage("{$context->attacker->name}'s focus intensifies!");
        }
    }

    /**
     * Prevent focus from being spent on attacks
     *
     * @return bool True to prevent spending, false to allow
     */
    public function beforeFocusSpent(CombatContext $context): bool
    {
        if ($context->attacker->has_focus) {
            // Channeling maintains focus - return true to prevent spending
            return true;
        }

        return false;
    }
}
