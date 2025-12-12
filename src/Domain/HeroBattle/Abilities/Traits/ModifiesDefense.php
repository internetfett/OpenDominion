<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use OpenDominion\Models\HeroCombatant;

/**
 * Trait for abilities that modify defense stat
 */
interface ModifiesDefense
{
    /**
     * Modify the defense stat
     *
     * @param HeroCombatant $combatant
     * @param int $currentDefense
     * @return int Modified defense value
     */
    public function modifyDefense(HeroCombatant $combatant, int $currentDefense): int;
}
