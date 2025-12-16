<?php

namespace OpenDominion\Tests\Unit\Domain\HeroBattle\Abilities\Special;

use Mockery as m;
use OpenDominion\Domain\HeroBattle\Abilities\Special\Undying;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class UndyingTest extends AbstractBrowserKitTestCase
{
    public function testStartsCountdownOnDeath()
    {
        $ability = new Undying([
            'attributes' => [
                'turns' => 5,
            ],
        ]);

        $combatant = m::mock(HeroCombatant::class);
        $combatant->current_health = 0;
        $combatant->status = [];
        $combatant->shouldReceive('update')->once()->with(m::on(function ($data) {
            return isset($data['status']['undying']) && $data['status']['undying'] === 5;
        }));

        $message = $ability->processStatus($combatant);

        $this->assertStringContainsString('will return from the dead in 5 turns', $message);
    }

    public function testCountsDownEachTurn()
    {
        $ability = new Undying([
            'attributes' => [
                'turns' => 5,
            ],
        ]);

        $combatant = m::mock(HeroCombatant::class);
        $combatant->current_health = 0;
        $combatant->status = ['undying' => 3];
        $combatant->shouldReceive('update')->once()->with(m::on(function ($data) {
            return isset($data['status']['undying']) && $data['status']['undying'] === 2;
        }));

        $message = $ability->processStatus($combatant);

        $this->assertStringContainsString('will return from the dead in 2 turns', $message);
    }

    public function testResurrectsAfterCountdown()
    {
        $ability = new Undying([
            'attributes' => [
                'turns' => 5,
            ],
        ]);

        $combatant = m::mock(HeroCombatant::class);
        $combatant->current_health = 0;
        $combatant->health = 100;
        $combatant->status = ['undying' => 1];
        $combatant->shouldReceive('update')->once()->with(m::on(function ($data) {
            return $data['current_health'] === 50
                && $data['health'] === 50
                && $data['has_focus'] === false
                && !isset($data['status']['undying']);
        }));

        $message = $ability->processStatus($combatant);

        $this->assertStringContainsString('has returned to life', $message);
    }

    public function testDoesNothingWhenAlive()
    {
        $ability = new Undying([
            'attributes' => [
                'turns' => 5,
            ],
        ]);

        $combatant = m::mock(HeroCombatant::class);
        $combatant->current_health = 50;
        $combatant->shouldReceive('update')->never();

        $message = $ability->processStatus($combatant);

        $this->assertEquals('', $message);
    }
}
