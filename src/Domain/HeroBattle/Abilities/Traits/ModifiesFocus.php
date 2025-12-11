<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

interface ModifiesFocus
{
    /**
     * Called after focus action is used
     */
    public function afterFocus(CombatContext $context): void;

    /**
     * Called when focus would be spent on attack
     */
    public function beforeFocusSpent(CombatContext $context): bool;
}
