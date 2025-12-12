<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Special;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\TriggersOnDeath;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;

class Hardiness extends AbstractAbility implements TriggersOnDeath
{
    /**
     * Prevents death once, leaving the combatant at 1 HP
     */
    public function beforeDeath(CombatContext $context): bool
    {
        if ($this->hasCharges()) {
            $this->consume();
            $context->target->current_health = 1;
            $context->addMessage("{$context->target->name} clings to life with 1 health.");

            return false; // Prevent death
        }

        return true; // Allow death
    }
}
