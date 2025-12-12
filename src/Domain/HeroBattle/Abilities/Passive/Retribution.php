<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesCounter;
use OpenDominion\Models\HeroCombatant;

/**
 * Retribution Ability
 *
 * Increases counter stat by 15, making counter-attacks more powerful
 */
class Retribution extends AbstractAbility implements ModifiesCounter
{
    /**
     * Add counter bonus
     */
    public function modifyCounter(HeroCombatant $combatant, int $currentCounter): int
    {
        return $currentCounter + 15;
    }
}
