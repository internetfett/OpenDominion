<?php

namespace OpenDominion\Tests\Unit\Domain\HeroBattle\Abilities\Special;

use Mockery as m;
use OpenDominion\Domain\HeroBattle\Abilities\Special\PowerSource;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class PowerSourceTest extends AbstractBrowserKitTestCase
{
    public function testReducesTargetStatsOnDeath()
    {
        $ability = new PowerSource([]);

        $battle = m::mock(HeroBattle::class);

        $combatant = m::mock(HeroCombatant::class);
        $combatant->name = 'Power Crystal';
        $combatant->current_health = 0;
        $combatant->status = [
            'power_source' => [
                'target_name' => 'The Ancient Dragon',
                'stat_reductions' => [
                    'attack' => 20,
                    'defense' => 15,
                ],
            ],
        ];

        $target = m::mock(HeroCombatant::class);
        $target->name = 'The Ancient Dragon';
        $target->attack = 100;
        $target->defense = 80;
        $target->shouldReceive('save')->once();

        $combatants = collect([$combatant, $target]);
        $battle->combatants = $combatants;

        $message = $ability->onDeath($combatant, $battle, collect([$target]));

        $this->assertEquals(80, $target->attack);
        $this->assertEquals(65, $target->defense);
        $this->assertStringContainsString('crumbles to dust', $message);
        $this->assertStringContainsString('severing its connection', $message);
        $this->assertStringContainsString('attack -20', $message);
        $this->assertStringContainsString('defense -15', $message);
    }

    public function testHandlesMissingConfiguration()
    {
        $ability = new PowerSource([]);

        $battle = m::mock(HeroBattle::class);

        $combatant = m::mock(HeroCombatant::class);
        $combatant->name = 'Power Crystal';
        $combatant->current_health = 0;
        $combatant->status = [];

        $battle->combatants = collect([$combatant]);

        $message = $ability->onDeath($combatant, $battle, collect());

        $this->assertEquals('', $message);
    }

    public function testHandlesMissingTarget()
    {
        $ability = new PowerSource([]);

        $battle = m::mock(HeroBattle::class);

        $combatant = m::mock(HeroCombatant::class);
        $combatant->name = 'Power Crystal';
        $combatant->current_health = 0;
        $combatant->status = [
            'power_source' => [
                'target_name' => 'Nonexistent Enemy',
                'stat_reductions' => [
                    'attack' => 20,
                ],
            ],
        ];

        $battle->combatants = collect([$combatant]);

        $message = $ability->onDeath($combatant, $battle, collect());

        $this->assertEquals('', $message);
    }
}
