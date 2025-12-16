<?php

namespace OpenDominion\Tests\Unit\Domain\HeroBattle\Abilities\Periodic;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Periodic\Darkness;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class DarknessTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testForcesDarknessOnTriggerTurns()
    {
        $ability = new Darkness([
            'attributes' => [
                'turns' => 2,
                'stat' => 'evasion',
                'value' => 20,
            ],
        ]);

        $battle = factory(HeroBattle::class)->create(['current_turn' => 1]);
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
            'evasion' => 50, // Not maxed
        ]);

        $suggestions = $ability->suggestActions($combatant, $battle, collect([$combatant]));

        $this->assertCount(1, $suggestions);
        $this->assertEquals('darkness', $suggestions->first()->action);
        $this->assertEquals(ActionSuggestion::PRIORITY_FORCED, $suggestions->first()->priority);
        $this->assertEquals('Darkness period trigger', $suggestions->first()->reason);
    }

    public function testDoesNotForceDarknessOnNonTriggerTurns()
    {
        $ability = new Darkness([
            'attributes' => [
                'turns' => 2,
                'stat' => 'evasion',
                'value' => 20,
            ],
        ]);

        $battle = factory(HeroBattle::class)->create(['current_turn' => 2]); // Turn 2 is not a trigger turn
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
            'evasion' => 50,
        ]);

        $suggestions = $ability->suggestActions($combatant, $battle, collect([$combatant]));

        $this->assertCount(0, $suggestions);
    }

    public function testDoesNotForceWhenEvasionMaxed()
    {
        $ability = new Darkness([
            'attributes' => [
                'turns' => 2,
                'stat' => 'evasion',
                'value' => 20,
            ],
        ]);

        $battle = factory(HeroBattle::class)->create(['current_turn' => 1]); // Trigger turn
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
            'evasion' => 100, // Maxed evasion
        ]);

        $suggestions = $ability->suggestActions($combatant, $battle, collect([$combatant]));

        $this->assertCount(0, $suggestions);
    }

    public function testPeriodicityWithDifferentTurns()
    {
        $ability = new Darkness([
            'attributes' => [
                'turns' => 2,
                'stat' => 'evasion',
                'value' => 20,
            ],
        ]);

        // Test turns 1-10
        $expectedTriggers = [1, 3, 5, 7, 9]; // Every 2 turns
        $actualTriggers = [];

        for ($turn = 1; $turn <= 10; $turn++) {
            $battle = factory(HeroBattle::class)->create(['current_turn' => $turn]);
            $combatant = factory(HeroCombatant::class)->create([
                'hero_battle_id' => $battle->id,
                'evasion' => 50,
            ]);

            $suggestions = $ability->suggestActions($combatant, $battle, collect([$combatant]));

            if ($suggestions->count() > 0) {
                $actualTriggers[] = $turn;
            }
        }

        $this->assertEquals($expectedTriggers, $actualTriggers);
    }
}
