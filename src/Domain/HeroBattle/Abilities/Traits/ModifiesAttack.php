<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use OpenDominion\Models\HeroCombatant;

/**
 * Trait for abilities that modify attack stat
 */
interface ModifiesAttack
{
    /**
     * Modify the attack stat
     *
     * @param HeroCombatant $combatant
     * @param int $currentAttack
     * @return int Modified attack value
     */
    public function modifyAttack(HeroCombatant $combatant, int $currentAttack): int;
}
