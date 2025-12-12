<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\StatusEffect\Weakened;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class WeakenedTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testWeakenedReducesDefenseBy15()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 50;

        $weakened = new Weakened('weakened', []);
        $modifiedDefense = $weakened->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(35, $modifiedDefense); // 50 - 15
    }

    public function testWeakenedWorksWithLowDefense()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 10;

        $weakened = new Weakened('weakened', []);
        $modifiedDefense = $weakened->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(-5, $modifiedDefense); // 10 - 15 (can go negative)
    }

    public function testWeakenedWorksWithHighDefense()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 100;

        $weakened = new Weakened('weakened', []);
        $modifiedDefense = $weakened->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(85, $modifiedDefense); // 100 - 15
    }

    public function testWeakenedWorksWithZeroDefense()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 0;

        $weakened = new Weakened('weakened', []);
        $modifiedDefense = $weakened->modifyDefense($combatant, $combatant->defense);

        $this->assertEquals(-15, $modifiedDefense); // 0 - 15
    }

    public function testWeakenedIsAlwaysActive()
    {
        $weakened = new Weakened('weakened', []);

        // Weakened should reduce defense regardless of health
        $combatant = new HeroCombatant();
        $combatant->defense = 40;

        foreach ([100, 50, 25, 1] as $health) {
            $combatant->current_health = $health;
            $modifiedDefense = $weakened->modifyDefense($combatant, $combatant->defense);
            $this->assertEquals(25, $modifiedDefense, "Should be -15 at {$health} health");
        }
    }
}
