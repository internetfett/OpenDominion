<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\ArcaneShield;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class ArcaneShieldTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testArcaneShieldAddsConstantDefenseBonus()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 25;
        $combatant->current_health = 100; // Full health

        $arcaneShield = new ArcaneShield('arcane_shield', []);
        $modifiedDefense = $arcaneShield->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(35, $modifiedDefense); // 25 + 10
    }

    public function testArcaneShieldWorksAtLowHealth()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 25;
        $combatant->current_health = 10; // Low health

        $arcaneShield = new ArcaneShield('arcane_shield', []);
        $modifiedDefense = $arcaneShield->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(35, $modifiedDefense); // Always +10
    }

    public function testArcaneShieldWorksWithZeroBaseDefense()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 0;
        $combatant->current_health = 50;

        $arcaneShield = new ArcaneShield('arcane_shield', []);
        $modifiedDefense = $arcaneShield->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(10, $modifiedDefense); // 0 + 10
    }

    public function testArcaneShieldWorksWithHighBaseDefense()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 100;
        $combatant->current_health = 80;

        $arcaneShield = new ArcaneShield('arcane_shield', []);
        $modifiedDefense = $arcaneShield->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(110, $modifiedDefense); // 100 + 10
    }

    public function testArcaneShieldIsAlwaysActive()
    {
        $arcaneShield = new ArcaneShield('arcane_shield', []);

        // Test at various health levels
        $combatant = new HeroCombatant();
        $combatant->defense = 20;

        foreach ([100, 75, 50, 25, 1] as $health) {
            $combatant->current_health = $health;
            $modifiedDefense = $arcaneShield->modifyDefense($combatant, $combatant->defense);
            $this->assertEquals(30, $modifiedDefense, "Should be +10 at {$health} health");
        }
    }
}
