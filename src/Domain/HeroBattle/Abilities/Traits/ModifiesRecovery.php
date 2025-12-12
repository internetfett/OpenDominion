<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Traits;

use OpenDominion\Models\HeroCombatant;

/**
 * Trait for abilities that modify recovery stat
 */
interface ModifiesRecovery
{
    /**
     * Modify the recovery stat
     *
     * @param HeroCombatant $combatant
     * @param int $currentRecovery
     * @return int Modified recovery value
     */
    public function modifyRecovery(HeroCombatant $combatant, int $currentRecovery): int;
}
