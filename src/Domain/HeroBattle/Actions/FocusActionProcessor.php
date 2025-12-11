<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Focus Action Processor
 *
 * Sets has_focus flag which adds bonus damage to next attack.
 * Channeling ability allows stacking focus bonus.
 */
class FocusActionProcessor extends AbstractActionProcessor
{
    public function process(CombatContext $context): void
    {
        // Set focus flag
        $context->attacker->has_focus = true;

        // No damage or healing
        $context->damage = 0;
        $context->healing = 0;

        // Add message
        $message = $this->getMessage($context, 'focus', $context->attacker->name);
        $context->addMessage($message);
    }
}
