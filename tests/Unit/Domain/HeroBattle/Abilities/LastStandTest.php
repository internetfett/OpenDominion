<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\LastStand;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class LastStandTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testLastStandIncreasesAttackWhenHealthLow()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 100;
        $combatant->current_health = 30; // Below 40 threshold

        $lastStand = new LastStand('last_stand', []);
        $modifiedAttack = $lastStand->modifyAttack($combatant, $combatant->attack);

        $this->assertEquals(110, $modifiedAttack); // 100 * 1.1
    }

    public function testLastStandIncreasesDefenseWhenHealthLow()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 50;
        $combatant->current_health = 25; // Below 40 threshold

        $lastStand = new LastStand('last_stand', []);
        $modifiedDefense = $lastStand->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(55, $modifiedDefense); // 50 * 1.1
    }

    public function testLastStandIncreasesRecoveryWhenHealthLow()
    {
        $combatant = new HeroCombatant();
        $combatant->recover = 20;
        $combatant->current_health = 15; // Below 40 threshold

        $lastStand = new LastStand('last_stand', []);
        $modifiedRecovery = $lastStand->modifyRecovery($combatant, $combatant->recover);

        $this->assertEquals(22, $modifiedRecovery); // 20 * 1.1 = 22
    }

    public function testLastStandDoesNotTriggerAboveHealthThreshold()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 100;
        $combatant->defense = 50;
        $combatant->recover = 20;
        $combatant->current_health = 50; // Above 40 threshold

        $lastStand = new LastStand('last_stand', []);

        $this->assertEquals(100, $lastStand->modifyAttack($combatant, $combatant->attack));
        $this->assertEquals(50, $lastStand->modifyDefense($combatant, $combatant->defense));
        $this->assertEquals(20, $lastStand->modifyRecovery($combatant, $combatant->recover));
    }

    public function testLastStandTriggersAtExactThreshold()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 100;
        $combatant->defense = 50;
        $combatant->recover = 20;
        $combatant->current_health = 40; // Exactly at threshold

        $lastStand = new LastStand('last_stand', []);

        $this->assertEquals(110, $lastStand->modifyAttack($combatant, $combatant->attack));
        $this->assertEquals(55, $lastStand->modifyDefense($combatant, $combatant->defense));
        $this->assertEquals(22, $lastStand->modifyRecovery($combatant, $combatant->recover));
    }

    public function testLastStandRoundsCorrectly()
    {
        $combatant = new HeroCombatant();
        $combatant->current_health = 30;

        $lastStand = new LastStand('last_stand', []);

        // Test rounding for attack
        $combatant->attack = 47; // 47 * 1.1 = 51.7, should round to 52
        $this->assertEquals(52, $lastStand->modifyAttack($combatant, $combatant->attack));

        // Test rounding for defense
        $combatant->defense = 23; // 23 * 1.1 = 25.3, should round to 25
        $this->assertEquals(25, $lastStand->modifyDefense($combatant, $combatant->defense));

        // Test rounding for recovery
        $combatant->recover = 15; // 15 * 1.1 = 16.5, should round to 17 (rounds up)
        $this->assertEquals(17, $lastStand->modifyRecovery($combatant, $combatant->recover));
    }

    public function testLastStandWorksAtCriticalHealth()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 50;
        $combatant->defense = 30;
        $combatant->recover = 10;
        $combatant->current_health = 1; // Critical health

        $lastStand = new LastStand('last_stand', []);

        $this->assertEquals(55, $lastStand->modifyAttack($combatant, $combatant->attack));
        $this->assertEquals(33, $lastStand->modifyDefense($combatant, $combatant->defense));
        $this->assertEquals(11, $lastStand->modifyRecovery($combatant, $combatant->recover));
    }

    public function testLastStandMultipliesAllStatsEqually()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 100;
        $combatant->defense = 100;
        $combatant->recover = 100;
        $combatant->current_health = 30;

        $lastStand = new LastStand('last_stand', []);

        // All stats should get the same 10% multiplier
        $this->assertEquals(110, $lastStand->modifyAttack($combatant, $combatant->attack));
        $this->assertEquals(110, $lastStand->modifyDefense($combatant, $combatant->defense));
        $this->assertEquals(110, $lastStand->modifyRecovery($combatant, $combatant->recover));
    }

    public function testLastStandWorksWithLowStats()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 5;
        $combatant->defense = 3;
        $combatant->recover = 2;
        $combatant->current_health = 10;

        $lastStand = new LastStand('last_stand', []);

        // 5 * 1.1 = 5.5 → 6 (rounds up)
        $this->assertEquals(6, $lastStand->modifyAttack($combatant, $combatant->attack));
        // 3 * 1.1 = 3.3 → 3 (rounds down)
        $this->assertEquals(3, $lastStand->modifyDefense($combatant, $combatant->defense));
        // 2 * 1.1 = 2.2 → 2 (rounds down)
        $this->assertEquals(2, $lastStand->modifyRecovery($combatant, $combatant->recover));
    }
}
