<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use OpenDominion\Models\HeroCombatant;

/**
 * Abilities that modify counter stat
 */
interface ModifiesCounter
{
    /**
     * Modify counter stat
     *
     * @param HeroCombatant $combatant The combatant whose counter is being modified
     * @param int $currentCounter The current counter value
     * @return int The modified counter value
     */
    public function modifyCounter(HeroCombatant $combatant, int $currentCounter): int;
}
