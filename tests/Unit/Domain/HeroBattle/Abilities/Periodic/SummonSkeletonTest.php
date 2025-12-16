<?php

namespace OpenDominion\Tests\Unit\Domain\HeroBattle\Abilities\Periodic;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Periodic\SummonSkeleton;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class SummonSkeletonTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testForcesSummonOnTriggerTurns()
    {
        $ability = new SummonSkeleton([
            'attributes' => [
                'turns' => 4,
                'enemy' => 'skeleton_warrior',
            ],
        ]);

        $battle = factory(HeroBattle::class)->create(['current_turn' => 1]);
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
        ]);

        $suggestions = $ability->suggestActions($combatant, $battle, collect([$combatant]));

        $this->assertCount(1, $suggestions);
        $this->assertEquals('summon_skeleton', $suggestions->first()->action);
        $this->assertEquals(ActionSuggestion::PRIORITY_FORCED, $suggestions->first()->priority);
        $this->assertEquals('Summon period trigger', $suggestions->first()->reason);
    }

    public function testDoesNotForceSummonOnNonTriggerTurns()
    {
        $ability = new SummonSkeleton([
            'attributes' => [
                'turns' => 4,
                'enemy' => 'skeleton_warrior',
            ],
        ]);

        $battle = factory(HeroBattle::class)->create(['current_turn' => 2]); // Turn 2 is not a trigger turn
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
        ]);

        $suggestions = $ability->suggestActions($combatant, $battle, collect([$combatant]));

        $this->assertCount(0, $suggestions);
    }

    public function testPeriodicityWithDifferentTurns()
    {
        $ability = new SummonSkeleton([
            'attributes' => [
                'turns' => 4,
                'enemy' => 'skeleton_warrior',
            ],
        ]);

        // Test turns 1-12
        $expectedTriggers = [1, 5, 9]; // Every 4 turns
        $actualTriggers = [];

        for ($turn = 1; $turn <= 12; $turn++) {
            $battle = factory(HeroBattle::class)->create(['current_turn' => $turn]);
            $combatant = factory(HeroCombatant::class)->create([
                'hero_battle_id' => $battle->id,
            ]);

            $suggestions = $ability->suggestActions($combatant, $battle, collect([$combatant]));

            if ($suggestions->count() > 0) {
                $actualTriggers[] = $turn;
            }
        }

        $this->assertEquals($expectedTriggers, $actualTriggers);
    }
}
