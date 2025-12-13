<?php

namespace Tests\Unit\Domain\HeroBattle\Combat;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Enrage;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Rally;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Retribution;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\LastStand;
use OpenDominion\Domain\HeroBattle\Combat\CombatCalculator;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class CombatCalculatorTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    protected CombatCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CombatCalculator();
    }

    public function testGetBaseCombatStatsLevel0()
    {
        $stats = $this->calculator->getBaseCombatStats(0);

        $this->assertEquals(80, $stats['health']);
        $this->assertEquals(40, $stats['attack']);
        $this->assertEquals(20, $stats['defense']);
        $this->assertEquals(10, $stats['evasion']);
        $this->assertEquals(10, $stats['focus']);
        $this->assertEquals(10, $stats['counter']);
        $this->assertEquals(20, $stats['recover']);
    }

    public function testGetBaseCombatStatsLevel10()
    {
        $stats = $this->calculator->getBaseCombatStats(10);

        // Health increases by 5 per level
        $this->assertEquals(130, $stats['health']); // 80 + (5 * 10)
        $this->assertEquals(40, $stats['attack']);
        $this->assertEquals(20, $stats['defense']);
    }

    public function testCalculateAttackWithoutAbilities()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 50;

        $attack = $this->calculator->calculateAttack($combatant, collect());

        $this->assertEquals(50, $attack);
    }

    public function testCalculateAttackWithEnrage()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 50;
        $combatant->current_health = 30; // Below threshold

        $enrage = new Enrage('enrage', []);
        $abilities = collect([$enrage]);

        $attack = $this->calculator->calculateAttack($combatant, $abilities);

        $this->assertEquals(60, $attack); // 50 + 10
    }

    public function testCalculateAttackWithMultipleAbilities()
    {
        $combatant = new HeroCombatant();
        $combatant->attack = 50;
        $combatant->current_health = 30;

        $enrage = new Enrage('enrage', []);
        $lastStand = new LastStand('last_stand', []);
        $abilities = collect([$enrage, $lastStand]);

        $attack = $this->calculator->calculateAttack($combatant, $abilities);

        // Enrage: 50 + 10 = 60
        // LastStand: 60 * 1.1 = 66
        $this->assertEquals(66, $attack);
    }

    public function testCalculateDefenseWithoutAbilities()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 30;

        $defense = $this->calculator->calculateDefense($combatant, collect());

        $this->assertEquals(30, $defense);
    }

    public function testCalculateDefenseWithRally()
    {
        $combatant = new HeroCombatant();
        $combatant->defense = 30;
        $combatant->current_health = 40; // At threshold

        $rally = new Rally('rally', []);
        $abilities = collect([$rally]);

        $defense = $this->calculator->calculateDefense($combatant, $abilities);

        $this->assertEquals(35, $defense); // 30 + 5
    }

    public function testCalculateRecoveryWithLastStand()
    {
        $combatant = new HeroCombatant();
        $combatant->recover = 20;
        $combatant->current_health = 25;

        $lastStand = new LastStand('last_stand', []);
        $abilities = collect([$lastStand]);

        $recovery = $this->calculator->calculateRecovery($combatant, $abilities);

        $this->assertEquals(22, $recovery); // 20 * 1.1 = 22
    }

    public function testCalculateCombatDamageBasic()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;

        $actionDef = ['attributes' => []];

        $damage = $this->calculator->calculateCombatDamage(
            $attacker,
            $target,
            'attack',
            'attack',
            $actionDef,
            collect(),
            collect()
        );

        $this->assertEquals(30, $damage); // 50 - 20
    }

    public function testCalculateCombatDamageWithFocus()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;
        $attacker->focus = 15;
        $attacker->has_focus = true;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;

        $actionDef = ['attributes' => []];

        $damage = $this->calculator->calculateCombatDamage(
            $attacker,
            $target,
            'attack',
            'attack',
            $actionDef,
            collect(),
            collect()
        );

        $this->assertEquals(45, $damage); // (50 + 15) - 20
    }

    public function testCalculateCombatDamageWithDefendAction()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;

        $actionDef = ['attributes' => ['defend' => 10]];

        $damage = $this->calculator->calculateCombatDamage(
            $attacker,
            $target,
            'attack',
            'defend',
            $actionDef,
            collect(),
            collect()
        );

        // Target defending: (20 * 2) + 10 = 50
        $this->assertEquals(0, $damage); // 50 - 50 = 0
    }

    public function testCalculateCombatDamageWithAbilities()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;
        $attacker->current_health = 30;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;
        $target->current_health = 35;

        $actionDef = ['attributes' => []];

        $enrage = new Enrage('enrage', []);
        $rally = new Rally('rally', []);

        $damage = $this->calculator->calculateCombatDamage(
            $attacker,
            $target,
            'attack',
            'attack',
            $actionDef,
            collect([$enrage]),
            collect([$rally])
        );

        // Attacker: 50 + 10 (enrage) = 60
        // Target: 20 + 5 (rally) = 25
        $this->assertEquals(35, $damage); // 60 - 25
    }

    public function testCalculateCombatDamageNeverNegative()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 10;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 50;

        $actionDef = ['attributes' => []];

        $damage = $this->calculator->calculateCombatDamage(
            $attacker,
            $target,
            'attack',
            'attack',
            $actionDef,
            collect(),
            collect()
        );

        $this->assertEquals(0, $damage); // Never negative
    }

    public function testCalculateCombatHeal()
    {
        $combatant = new HeroCombatant();
        $combatant->recover = 25;
        $combatant->current_health = 30;

        $lastStand = new LastStand('last_stand', []);
        $abilities = collect([$lastStand]);

        $healing = $this->calculator->calculateCombatHeal($combatant, $abilities);

        $this->assertEquals(28, $healing); // 25 * 1.1 = 27.5, rounded to 28
    }

    public function testCalculateCounterWithoutAbilities()
    {
        $combatant = new HeroCombatant();
        $combatant->counter = 25;

        $counter = $this->calculator->calculateCounter($combatant, collect());

        $this->assertEquals(25, $counter);
    }

    public function testCalculateCounterWithRetribution()
    {
        $combatant = new HeroCombatant();
        $combatant->counter = 25;

        $retribution = new Retribution('retribution', []);
        $abilities = collect([$retribution]);

        $counter = $this->calculator->calculateCounter($combatant, $abilities);

        $this->assertEquals(40, $counter); // 25 + 15
    }
}
