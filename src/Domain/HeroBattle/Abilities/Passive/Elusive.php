<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesEvasion;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;

class Elusive extends AbstractAbility implements ModifiesEvasion
{
    /**
     * Prevents evasion from being bypassed unless attacker has focus
     */
    public function beforeDamageReceived(CombatContext $context): void
    {
        if (!$context->hasFocus()) {
            // Set evade multiplier to 0 means complete evasion cannot be bypassed
            $context->evadeMultiplier = 0;
        }
    }
}
