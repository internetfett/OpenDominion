<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

interface ModifiesEvasion
{
    /**
     * Called before damage is received to modify evasion
     */
    public function beforeDamageReceived(CombatContext $context): void;
}
