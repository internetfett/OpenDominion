<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Counter Action Processor
 *
 * Sets up counter stance. If opponent attacks, counter damage is applied.
 * Actual counter damage is calculated in AttackActionProcessor.
 */
class CounterActionProcessor extends AbstractActionProcessor
{
    public function process(CombatContext $context): void
    {
        // No immediate damage or healing
        $context->damage = 0;
        $context->healing = 0;

        // Add message
        $message = $this->getMessage($context, 'counter', $context->attacker->name);
        $context->addMessage($message);
    }
}
