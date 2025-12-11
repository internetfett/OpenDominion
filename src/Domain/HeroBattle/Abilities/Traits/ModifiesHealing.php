<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

interface ModifiesHealing
{
    /**
     * Called after healing is calculated
     */
    public function afterHealingCalculated(CombatContext $context): void;
}
