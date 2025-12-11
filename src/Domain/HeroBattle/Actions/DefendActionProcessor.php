<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Defend Action Processor
 *
 * Doubles the combatant's defense for the turn.
 * Defense doubling is handled in HeroCalculator when it sees current_action === 'defend'
 */
class DefendActionProcessor extends AbstractActionProcessor
{
    public function process(CombatContext $context): void
    {
        // No damage or healing - defense bonus applied by calculator
        $context->damage = 0;
        $context->healing = 0;

        // Add message
        $message = $this->getMessage($context, 'defend', $context->attacker->name);
        $context->addMessage($message);
    }
}
