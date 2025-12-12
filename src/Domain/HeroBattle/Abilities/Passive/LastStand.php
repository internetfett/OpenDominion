<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesAttack;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesDefense;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesRecovery;
use OpenDominion\Models\HeroCombatant;

/**
 * Last Stand Ability
 *
 * Provides a 10% multiplier to all combat stats when health is low (40 or below)
 */
class LastStand extends AbstractAbility implements ModifiesAttack, ModifiesDefense, ModifiesRecovery
{
    protected const HEALTH_THRESHOLD = 40;
    protected const STAT_MULTIPLIER = 1.1;

    /**
     * Modify attack when health is low
     */
    public function modifyAttack(HeroCombatant $combatant, int $currentAttack): int
    {
        if ($combatant->current_health <= self::HEALTH_THRESHOLD) {
            return (int) round($currentAttack * self::STAT_MULTIPLIER);
        }

        return $currentAttack;
    }

    /**
     * Modify defense when health is low
     */
    public function modifyDefense(HeroCombatant $combatant, int $currentDefense): int
    {
        if ($combatant->current_health <= self::HEALTH_THRESHOLD) {
            return (int) round($currentDefense * self::STAT_MULTIPLIER);
        }

        return $currentDefense;
    }

    /**
     * Modify recovery when health is low
     */
    public function modifyRecovery(HeroCombatant $combatant, int $currentRecovery): int
    {
        if ($combatant->current_health <= self::HEALTH_THRESHOLD) {
            return (int) round($currentRecovery * self::STAT_MULTIPLIER);
        }

        return $currentRecovery;
    }
}
