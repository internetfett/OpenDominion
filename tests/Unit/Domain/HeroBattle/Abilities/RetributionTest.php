<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Retribution;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class RetributionTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testRetributionAddsCounterBonus()
    {
        $combatant = new HeroCombatant();
        $combatant->counter = 30;

        $retribution = new Retribution('retribution', []);
        $modifiedCounter = $retribution->modifyCounter($combatant, $combatant->counter);

        $this->assertEquals(45, $modifiedCounter); // 30 + 15
    }

    public function testRetributionWorksWithZeroCounter()
    {
        $combatant = new HeroCombatant();
        $combatant->counter = 0;

        $retribution = new Retribution('retribution', []);
        $modifiedCounter = $retribution->modifyCounter($combatant, $combatant->counter);

        $this->assertEquals(15, $modifiedCounter); // 0 + 15
    }

    public function testRetributionWorksWithLowCounter()
    {
        $combatant = new HeroCombatant();
        $combatant->counter = 5;

        $retribution = new Retribution('retribution', []);
        $modifiedCounter = $retribution->modifyCounter($combatant, $combatant->counter);

        $this->assertEquals(20, $modifiedCounter); // 5 + 15
    }

    public function testRetributionWorksWithHighCounter()
    {
        $combatant = new HeroCombatant();
        $combatant->counter = 100;

        $retribution = new Retribution('retribution', []);
        $modifiedCounter = $retribution->modifyCounter($combatant, $combatant->counter);

        $this->assertEquals(115, $modifiedCounter); // 100 + 15
    }

    public function testRetributionIsAlwaysActive()
    {
        $retribution = new Retribution('retribution', []);

        // Retribution should work regardless of health or other conditions
        $combatant = new HeroCombatant();
        $combatant->counter = 20;

        foreach ([100, 50, 25, 1] as $health) {
            $combatant->current_health = $health;
            $modifiedCounter = $retribution->modifyCounter($combatant, $combatant->counter);
            $this->assertEquals(35, $modifiedCounter, "Should be +15 at {$health} health");
        }
    }
}
