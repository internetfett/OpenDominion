<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

interface TriggersOnDeath
{
    /**
     * Called before a combatant dies
     *
     * @return bool True to allow death, false to prevent it
     */
    public function beforeDeath(CombatContext $context): bool;
}
