<?php

namespace OpenDominion\Tests\Unit\Domain\HeroBattle\Abilities\Phased;

use Mockery as m;
use OpenDominion\Domain\HeroBattle\Abilities\Phased\TomeOfPower;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class TomeOfPowerTest extends AbstractBrowserKitTestCase
{
    public function testCalculatesCurrentPhase()
    {
        $ability = new TomeOfPower();

        // Turn 1-4: Phase 1
        $this->assertEquals(1, $ability->getCurrentPhase(1));
        $this->assertEquals(1, $ability->getCurrentPhase(4));

        // Turn 5-8: Phase 2
        $this->assertEquals(2, $ability->getCurrentPhase(5));
        $this->assertEquals(2, $ability->getCurrentPhase(8));

        // Turn 9-12: Phase 3
        $this->assertEquals(3, $ability->getCurrentPhase(9));

        // Turn 13-16: Phase 4
        $this->assertEquals(4, $ability->getCurrentPhase(13));

        // Turn 17+: Phase 5 (stays at max)
        $this->assertEquals(5, $ability->getCurrentPhase(17));
        $this->assertEquals(5, $ability->getCurrentPhase(100));
    }

    public function testGrantsAbilitiesOnPhaseChange()
    {
        $ability = new TomeOfPower();

        $battle = m::mock(HeroBattle::class);
        $battle->current_turn = 5; // Phase 2

        $combatant = m::mock(HeroCombatant::class);
        $combatant->current_health = 100;
        $combatant->status = []; // No previous phase
        $combatant->abilities = [];
        $combatant->shouldReceive('update')->once()->with(m::on(function ($data) {
            return isset($data['status']['tome_of_power_phase'])
                && $data['status']['tome_of_power_phase'] === 2;
        }));
        $combatant->shouldReceive('save')->once();

        $message = $ability->processPhase($combatant, $battle, collect());

        $this->assertStringContainsString('channels arcane energy', $message);
        $this->assertEquals(['channeling'], $combatant->abilities);
    }

    public function testDoesNotProcessWhenDead()
    {
        $ability = new TomeOfPower();

        $battle = m::mock(HeroBattle::class);
        $battle->current_turn = 5;

        $combatant = m::mock(HeroCombatant::class);
        $combatant->current_health = 0; // Dead
        $combatant->shouldReceive('update')->never();

        $message = $ability->processPhase($combatant, $battle, collect());

        $this->assertEquals('', $message);
    }

    public function testDoesNotProcessWhenPhaseUnchanged()
    {
        $ability = new TomeOfPower();

        $battle = m::mock(HeroBattle::class);
        $battle->current_turn = 6; // Still phase 2

        $combatant = m::mock(HeroCombatant::class);
        $combatant->current_health = 100;
        $combatant->status = ['tome_of_power_phase' => 2]; // Already in phase 2
        $combatant->shouldReceive('update')->never();

        $message = $ability->processPhase($combatant, $battle, collect());

        $this->assertEquals('', $message);
    }

    public function testPhase5GrantsAllAbilities()
    {
        $ability = new TomeOfPower();

        $battle = m::mock(HeroBattle::class);
        $battle->current_turn = 17; // Phase 5

        $combatant = m::mock(HeroCombatant::class);
        $combatant->current_health = 100;
        $combatant->status = ['tome_of_power_phase' => 4]; // Coming from phase 4
        $combatant->abilities = [];
        $combatant->shouldReceive('update')->once()->with(m::on(function ($data) {
            return isset($data['status']['tome_of_power_phase'])
                && $data['status']['tome_of_power_phase'] === 5;
        }));
        $combatant->shouldReceive('save')->once();

        $message = $ability->processPhase($combatant, $battle, collect());

        $this->assertStringContainsString('has mastered the Tome of Power', $message);
        $this->assertStringContainsString('cannot truly die', $message);
        $this->assertEquals(['channeling', 'lifesteal', 'power_strike', 'undying'], $combatant->abilities);
    }
}
