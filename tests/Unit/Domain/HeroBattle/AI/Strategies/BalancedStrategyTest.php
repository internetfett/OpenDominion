<?php

namespace OpenDominion\Tests\Unit\Domain\HeroBattle\AI\Strategies;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Domain\HeroBattle\AI\Strategies\BalancedStrategy;
use OpenDominion\Helpers\HeroHelper;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class BalancedStrategyTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    protected BalancedStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new BalancedStrategy(app(HeroHelper::class));
    }

    public function testCriticalHealthForcesRecover()
    {
        $battle = factory(HeroBattle::class)->create();
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
            'current_health' => 35, // Critical health (≤ 40)
            'health' => 100,
            'recover' => 20,
        ]);

        $suggestions = $this->strategy->suggestActions(
            $combatant,
            $battle,
            collect([$combatant]),
            collect()
        );

        $this->assertCount(1, $suggestions);
        $this->assertEquals('recover', $suggestions->first()->action);
        $this->assertEquals(ActionSuggestion::PRIORITY_CRITICAL, $suggestions->first()->priority);
    }

    public function testNormalHealthReturnsBalancedAction()
    {
        $battle = factory(HeroBattle::class)->create();
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
            'current_health' => 80,
            'health' => 100,
            'has_focus' => false,
        ]);

        $suggestions = $this->strategy->suggestActions(
            $combatant,
            $battle,
            collect([$combatant]),
            collect()
        );

        $this->assertCount(1, $suggestions);

        // Should be one of the available actions from balanced strategy
        $validActions = ['attack', 'defend', 'focus', 'counter', 'recover'];
        $this->assertContains($suggestions->first()->action, $validActions);
        $this->assertEquals(ActionSuggestion::PRIORITY_NORMAL, $suggestions->first()->priority);
    }

    public function testFocusNotSuggestedWhenAlreadyFocused()
    {
        $battle = factory(HeroBattle::class)->create();
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
            'current_health' => 80,
            'health' => 100,
            'has_focus' => true, // Already focused
        ]);

        // Run multiple times to ensure focus is never suggested
        for ($i = 0; $i < 20; $i++) {
            $suggestions = $this->strategy->suggestActions(
                $combatant,
                $battle,
                collect([$combatant]),
                collect()
            );

            $this->assertNotEquals('focus', $suggestions->first()->action);
        }
    }

    public function testRecoverNotSuggestedWhenAtFullHealth()
    {
        $battle = factory(HeroBattle::class)->create();
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
            'current_health' => 100,
            'health' => 100,
            'recover' => 20,
        ]);

        // Run multiple times to ensure recover is never suggested
        for ($i = 0; $i < 20; $i++) {
            $suggestions = $this->strategy->suggestActions(
                $combatant,
                $battle,
                collect([$combatant]),
                collect()
            );

            $this->assertNotEquals('recover', $suggestions->first()->action);
        }
    }

    public function testWeightedDistribution()
    {
        $battle = factory(HeroBattle::class)->create();
        $combatant = factory(HeroCombatant::class)->create([
            'hero_battle_id' => $battle->id,
            'current_health' => 80,
            'health' => 100,
            'has_focus' => false,
            'recover' => 20,
        ]);

        $actionCounts = [
            'attack' => 0,
            'defend' => 0,
            'focus' => 0,
            'counter' => 0,
            'recover' => 0,
        ];

        // Run 100 times to get distribution
        for ($i = 0; $i < 100; $i++) {
            $suggestions = $this->strategy->suggestActions(
                $combatant,
                $battle,
                collect([$combatant]),
                collect()
            );

            $action = $suggestions->first()->action;
            if (isset($actionCounts[$action])) {
                $actionCounts[$action]++;
            }
        }

        // Attack should appear most often (weight 4 out of 8 total)
        // This is probabilistic, so we just verify attack appears more than others
        $this->assertGreaterThan($actionCounts['defend'], $actionCounts['attack']);
        $this->assertGreaterThan($actionCounts['focus'], $actionCounts['attack']);
        $this->assertGreaterThan($actionCounts['counter'], $actionCounts['attack']);
        $this->assertGreaterThan($actionCounts['recover'], $actionCounts['attack']);
    }
}
