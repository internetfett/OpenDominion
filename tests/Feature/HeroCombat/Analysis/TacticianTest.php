<?php

namespace OpenDominion\Tests\Feature\HeroCombat\Analysis;

use OpenDominion\HeroCombat\Analysis\BattleSimulator;
use OpenDominion\HeroCombat\Analysis\Build;
use OpenDominion\HeroCombat\Analysis\BuildFactory;
use OpenDominion\HeroCombat\Analysis\TacticianProfile;
use OpenDominion\HeroCombat\Analysis\TacticianStrategy;
use OpenDominion\HeroCombat\Analysis\TurnPreview;
use OpenDominion\HeroCombat\Content\Effects\Marked;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\BattleState;
use OpenDominion\HeroCombat\Engine\InMemorySpawner;
use OpenDominion\HeroCombat\Engine\Intent;
use OpenDominion\HeroCombat\Engine\Random\SeededRandomSource;
use OpenDominion\Providers\HeroCombatServiceProvider;
use OpenDominion\Tests\AbstractTestCase;

class TacticianTest extends AbstractTestCase
{
    private function duel(Build $a = new Build(9), Build $b = new Build(9)): Battle
    {
        $battle = new Battle(
            new BattleState(id: null, turn: 1, seed: 1, mode: BattleState::MODE_PVP),
            HeroCombatServiceProvider::buildRegistry(),
            new SeededRandomSource(1),
            new InMemorySpawner(),
        );
        $builds = $this->app->make(BuildFactory::class);
        $builds->addTo($battle, $a, 1, 1, 'A', 'balanced');
        $builds->addTo($battle, $b, 2, 2, 'B', 'balanced');

        return $battle;
    }

    public function testPreviewWeightsEvasionExactly(): void
    {
        $battle = $this->duel();
        $battle->combatant(2)->baseStats['evasion'] = 50;

        $outcome = (new TurnPreview())->outcome($battle, [1 => 'attack', 2 => 'focus']);

        $hit = 40 - 20;
        $chance = 50 / 101;
        $expectedHealth = $battle->combatant(2)->currentHealth - ($hit * (1 - $chance) + ($hit / 2) * $chance);
        $this->assertEqualsWithDelta($expectedHealth, $outcome->health[2], 1e-9);
    }

    public function testOffenseIsTheDamageEdgeAgainstTheOpponent(): void
    {
        $battle = $this->duel();
        $a = $battle->combatant(1);
        $b = $battle->combatant(2);
        $counterThreat = TurnPreview::COUNTER_WEIGHT * (40 + 10 - 20);

        $this->assertEquals((40 - 20) + $counterThreat, TurnPreview::offense($battle, $a));

        $battle->effects->apply($b, 'marked');
        $battle->effects->apply($b, 'exposed', 2);
        $this->assertEquals((40 - 15) + Marked::BONUS_DAMAGE + TurnPreview::COUNTER_WEIGHT * (40 + 10 - 15), TurnPreview::offense($battle, $a), 'Marked and Exposed raise the edge');

        $battle->effects->apply($a, 'staggered');
        $this->assertEquals((40 - 15) + Marked::BONUS_DAMAGE, TurnPreview::offense($battle, $a), 'Staggered removes the counter threat');
    }

    public function testOffenseCountsBattleLongShiftsOverSeveralTurns(): void
    {
        $battle = $this->duel();
        $a = $battle->combatant(1);
        $b = $battle->combatant(2);
        $before = TurnPreview::offense($battle, $a);
        $theirsBefore = TurnPreview::offense($battle, $b);

        $battle->effects->apply($b, 'analyzed');
        $this->assertEquals((1 + TurnPreview::COUNTER_WEIGHT) * 1 + TurnPreview::LASTING_TURNS, TurnPreview::offense($battle, $a) - $before, '-1 defense now and for the turns ahead');

        $battle->effects->apply($b, 'outmaneuvered');
        $this->assertEquals(-(TurnPreview::COUNTER_WEIGHT * 2) * (1 + TurnPreview::LASTING_TURNS), TurnPreview::offense($battle, $b) - $theirsBefore, '-2 counter lowers their threat now and later');
    }

    public function testPreviewDoesNotChangeTheRealBattle(): void
    {
        $battle = $this->duel();
        $healthBefore = $battle->combatant(2)->currentHealth;

        (new TurnPreview())->outcome($battle, [1 => 'attack', 2 => 'defend']);

        $this->assertEquals($healthBefore, $battle->combatant(2)->currentHealth);
        $this->assertEquals(1, $battle->turn());
    }

    public function testTacticianNeverPeeksAtTheOpponentsChoice(): void
    {
        $strategy = new TacticianStrategy(TacticianProfile::presets()['balanced']);
        $battle = $this->duel();
        $me = $battle->combatant(1);
        $opponent = $battle->combatant(2);

        $baseline = $strategy->mix($me, $opponent, $battle);

        foreach (['attack', 'counter', 'defend'] as $choice) {
            $opponent->queue = [['ability' => $choice, 'target' => $me->id]];
            $battle->state->intents[$opponent->id] = new Intent($opponent->id, $choice, $me->id);

            $this->assertEquals($baseline, $strategy->mix($me, $opponent, $battle), "Mix changed when the opponent chose {$choice}");
        }
    }

    public function testTacticianRespectsCooldownsOnBothSides(): void
    {
        $strategy = new TacticianStrategy(TacticianProfile::presets()['balanced']);
        $battle = $this->duel();
        $battle->combatant(1)->cooldowns = ['counter' => 5, 'focus' => 5];

        $this->assertEquals(['attack', 'defend', 'recover'], $strategy->legalActions($battle->combatant(1), $battle));
    }

    public function testTacticianFinishesALethalOpponent(): void
    {
        $strategy = new TacticianStrategy(TacticianProfile::presets()['balanced']);
        $battle = $this->duel();
        $battle->combatant(2)->currentHealth = 5;
        $battle->combatant(2)->baseStats['defense'] = 0;
        $battle->combatant(2)->baseStats['evasion'] = 0;
        $battle->combatant(2)->cooldowns = ['counter' => 5, 'recover' => 5];

        $actions = $strategy->legalActions($battle->combatant(1), $battle);
        $mix = array_combine($actions, $strategy->mix($battle->combatant(1), $battle->combatant(2), $battle));

        $this->assertGreaterThan(0.9, $mix['attack'], 'Defending cannot save an opponent with no defense');
    }

    public function testTacticianOutplaysTheWeightedPreset(): void
    {
        $result = (new BattleSimulator($this->app->make(BuildFactory::class)))
            ->run(new Build(9), new Build(9), 'tactician:balanced', 'balanced', 40, 11);

        $this->assertGreaterThan(0.55, $result->scoreA());
    }

    public function testTacticianIsNotRegisteredInTheGame(): void
    {
        $registry = $this->app->make(\OpenDominion\HeroCombat\Registry\CombatRegistry::class);

        $this->assertFalse($registry->hasStrategy('tactician:balanced'));
    }

    public function testSimulationsAreDeterministic(): void
    {
        $simulator = new BattleSimulator($this->app->make(BuildFactory::class));

        $first = $simulator->run(new Build(9), new Build(9, techniques: ['backstab']), 'tactician:aggressive', 'tactician:defensive', 6, 3);
        $second = $simulator->run(new Build(9), new Build(9, techniques: ['backstab']), 'tactician:aggressive', 'tactician:defensive', 6, 3);

        $this->assertEquals([$first->winsA, $first->winsB, $first->draws, $first->totalTurns], [$second->winsA, $second->winsB, $second->draws, $second->totalTurns]);
    }
}
