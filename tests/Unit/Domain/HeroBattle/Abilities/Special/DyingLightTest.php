<?php

namespace OpenDominion\Tests\Unit\Domain\HeroBattle\Abilities\Special;

use Mockery as m;
use OpenDominion\Domain\HeroBattle\Abilities\Special\DyingLight;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class DyingLightTest extends AbstractBrowserKitTestCase
{
    public function testReducesNightbringerEvasionOnDeath()
    {
        $ability = new DyingLight([]);

        $battle = m::mock(HeroBattle::class);

        $combatant = m::mock(HeroCombatant::class);
        $combatant->name = 'Nox Cultist';
        $combatant->current_health = 0;

        $nightbringer = m::mock(HeroCombatant::class);
        $nightbringer->name = 'The Nightbringer';
        $nightbringer->evasion = 50;
        $nightbringer->shouldReceive('save')->once();

        $combatants = collect([$combatant, $nightbringer]);
        $battle->combatants = $combatants;

        $message = $ability->onDeath($combatant, $battle, collect());

        $this->assertEquals(0, $nightbringer->evasion);
        $this->assertStringContainsString('explodes in a blast of light', $message);
        $this->assertStringContainsString('exposing the Nightbringer', $message);
    }

    public function testWorksWithoutNightbringer()
    {
        $ability = new DyingLight([]);

        $battle = m::mock(HeroBattle::class);

        $combatant = m::mock(HeroCombatant::class);
        $combatant->name = 'Nox Cultist';
        $combatant->current_health = 0;

        $combatants = collect([$combatant]);
        $battle->combatants = $combatants;

        $message = $ability->onDeath($combatant, $battle, collect());

        $this->assertStringContainsString('explodes in a blast of light', $message);
        $this->assertStringNotContainsString('Nightbringer', $message);
    }
}
