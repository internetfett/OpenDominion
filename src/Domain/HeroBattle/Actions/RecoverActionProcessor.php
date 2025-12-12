<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Recover Action Processor
 *
 * Restores health based on combatant's recover stat.
 * Mending ability enhances healing when focused.
 */
class RecoverActionProcessor extends AbstractActionProcessor
{
    public function process(CombatContext $context): void
    {
        // Ensure abilities are loaded
        $attackerAbilities = $context->attackerAbilities ?? collect();

        // Calculate healing amount with ability modifiers
        $context->healing = $this->combatCalculator->calculateCombatHeal(
            $context->attacker,
            $attackerAbilities
        );

        // No damage dealt
        $context->damage = 0;

        // Add message
        $message = $this->getMessage(
            $context,
            'recover',
            $context->attacker->name,
            $context->healing
        );
        $context->addMessage($message);
    }
}
