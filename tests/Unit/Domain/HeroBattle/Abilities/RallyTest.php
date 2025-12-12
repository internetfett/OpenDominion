<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Rally;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class RallyTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testRallyIncreasesDefenseWhenHealthLow()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 30;
        $combatant->current_health = 25; // Below 40 threshold

        $rally = new Rally('rally', []);
        $modifiedDefense = $rally->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(35, $modifiedDefense); // 30 + 5
    }

    public function testRallyDoesNotTriggerAboveHealthThreshold()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 30;
        $combatant->current_health = 50; // Above 40 threshold

        $rally = new Rally('rally', []);
        $modifiedDefense = $rally->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(30, $modifiedDefense); // No change
    }

    public function testRallyTriggersAtExactThreshold()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 30;
        $combatant->current_health = 40; // Exactly at threshold

        $rally = new Rally('rally', []);
        $modifiedDefense = $rally->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(35, $modifiedDefense); // Should trigger
    }

    public function testRallyWorksWithVeryLowDefense()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 2;
        $combatant->current_health = 10;

        $rally = new Rally('rally', []);
        $modifiedDefense = $rally->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(7, $modifiedDefense); // 2 + 5
    }

    public function testRallyWorksWithCriticalHealth()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 20;
        $combatant->current_health = 1; // Critical health

        $rally = new Rally('rally', []);
        $modifiedDefense = $rally->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(25, $modifiedDefense); // 20 + 5
    }
}
