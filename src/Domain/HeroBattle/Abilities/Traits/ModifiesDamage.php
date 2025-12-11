<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

interface ModifiesDamage
{
    /**
     * Called after damage has been calculated but before it's applied
     */
    public function afterDamageDealt(CombatContext $context): void;
}
