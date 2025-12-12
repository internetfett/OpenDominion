<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Enrage;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class EnrageTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testEnrageIncreasesAttackWhenHealthLow()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 50;
        $combatant->current_health = 30; // Below 40 threshold

        $enrage = new Enrage('enrage', []);
        $modifiedAttack = $enrage->modifyAttack($combatant, $combatant->attack);

        $this->assertEquals(60, $modifiedAttack); // 50 + 10
    }

    public function testEnrageDoesNotTriggerAboveHealthThreshold()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 50;
        $combatant->current_health = 41; // Above 40 threshold

        $enrage = new Enrage('enrage', []);
        $modifiedAttack = $enrage->modifyAttack($combatant, $combatant->attack);

        $this->assertEquals(50, $modifiedAttack); // No change
    }

    public function testEnrageTriggersAtExactThreshold()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 50;
        $combatant->current_health = 40; // Exactly at threshold

        $enrage = new Enrage('enrage', []);
        $modifiedAttack = $enrage->modifyAttack($combatant, $combatant->attack);

        $this->assertEquals(60, $modifiedAttack); // Should trigger
    }

    public function testEnrageWorksWithZeroHealth()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 50;
        $combatant->current_health = 0;

        $enrage = new Enrage('enrage', []);
        $modifiedAttack = $enrage->modifyAttack($combatant, $combatant->attack);

        $this->assertEquals(60, $modifiedAttack); // Should still trigger
    }

    public function testEnrageWorksWithVeryLowAttack()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 5;
        $combatant->current_health = 10;

        $enrage = new Enrage('enrage', []);
        $modifiedAttack = $enrage->modifyAttack($combatant, $combatant->attack);

        $this->assertEquals(15, $modifiedAttack); // 5 + 10
    }
}
