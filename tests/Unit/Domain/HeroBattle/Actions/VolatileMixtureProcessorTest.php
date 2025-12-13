<?php

namespace Tests\Unit\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Actions\VolatileMixtureProcessor;
use OpenDominion\Domain\HeroBattle\Combat\CombatCalculator;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use PHPUnit\Framework\TestCase;

class VolatileMixtureProcessorTest extends TestCase
{

    protected VolatileMixtureProcessor $processor;
    protected CombatCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CombatCalculator();
        $this->processor = new VolatileMixtureProcessor($this->calculator, 'volatile_mixture');
    }

    public function testSuccessDealsBonus()
    {
        $battle = new HeroBattle();
        $battle->current_turn = 1;

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;

        $actionDef = [
            'attributes' => [
                'success_chance' => 1.0,  // Force success
                'attack_bonus' => 1.5,
            ],
            'messages' => [
                'success' => '%s hurls an unstable concoction, dealing %s damage to %s.',
            ]
        ];

        $context = new CombatContext($attacker, $target, $battle, 'volatile_mixture', 'attack', $actionDef);
        $context->attackerAbilities = collect();
        $context->targetAbilities = collect();

        $this->processor->process($context);

        // Base damage would be 30 (50 attack - 20 defense)
        // With 1.5x bonus: 45
        $this->assertEquals(45, $context->damage, 'Volatile mixture should deal bonus damage on success');
        $this->assertEquals(0, $context->healing, 'Should not heal attacker on success');
        $this->assertFalse($context->backfired, 'Should not backfire with 100% success chance');
    }

    public function testBackfireDamagesAttacker()
    {
        $battle = new HeroBattle(); $battle->current_turn = 1;

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;

        $actionDef = [
            'attributes' => [
                'success_chance' => 0.0,  // Force backfire
                'attack_bonus' => 1.5,
            ],
            'messages' => [
                'backfire' => '%s\'s volatile mixture explodes prematurely! %s is caught in the blast, taking %s damage.',
            ]
        ];

        $context = new CombatContext($attacker, $target, $battle, 'volatile_mixture', 'attack', $actionDef);
        $context->attackerAbilities = collect();
        $context->targetAbilities = collect();

        $this->processor->process($context);

        // Base damage would be 30 (50 - 20)
        // Backfire: attacker takes 30 damage
        $this->assertEquals(0, $context->damage, 'No damage to target on backfire');
        $this->assertEquals(-30, $context->healing, 'Attacker should take damage on backfire');
        $this->assertTrue($context->backfired, 'Should be marked as backfired');
    }

    public function testSuccessWithEvasion()
    {
        $battle = new HeroBattle(); $battle->current_turn = 1;

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;
        $target->evasion = 50;

        $actionDef = [
            'attributes' => [
                'success_chance' => 1.0,
                'attack_bonus' => 1.5,
                'evade' => true,  // Allow evasion
            ],
            'messages' => [
                'success_evaded' => '%s\'s explosive mixture detonates, but %s evades most of the blast, taking only %s damage.',
            ]
        ];

        $context = new CombatContext($attacker, $target, $battle, 'volatile_mixture', 'attack', $actionDef);
        $context->attackerAbilities = collect();
        $context->targetAbilities = collect();

        // Manually trigger evasion for test
        $context->evaded = true;
        $context->evadeMultiplier = 0.5;

        $this->processor->process($context);

        // Base damage: 30, with bonus: 45
        // After evasion (manually applied in processor): would be reduced
        $this->assertGreaterThan(0, $context->damage, 'Should still deal some damage with evasion');
    }

    public function testSuccessWithCounter()
    {
        $battle = new HeroBattle(); $battle->current_turn = 1;

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;
        $target->counter = 15;

        $actionDef = [
            'attributes' => [
                'success_chance' => 1.0,
                'attack_bonus' => 1.5,
            ],
            'messages' => [
                'success_countered' => '%s hurls an unstable concoction, dealing %s damage to %s, who then counters for %s damage.',
            ]
        ];

        $context = new CombatContext($attacker, $target, $battle, 'volatile_mixture', 'counter', $actionDef);
        $context->attackerAbilities = collect();
        $context->targetAbilities = collect();

        $this->processor->process($context);

        $this->assertGreaterThan(0, $context->damage, 'Should deal damage to target');
        $this->assertTrue($context->countered, 'Counter flag should be set');
        $this->assertLessThan(0, $context->healing, 'Attacker should take counter damage');
    }

    public function testBackfireWithCounter()
    {
        $battle = new HeroBattle(); $battle->current_turn = 1;

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;
        $target->counter = 15;

        $actionDef = [
            'attributes' => [
                'success_chance' => 0.0,  // Force backfire
                'attack_bonus' => 1.5,
            ],
            'messages' => [
                'backfire_countered' => '%s\'s volatile mixture explodes prematurely! %s is caught in the blast for %s damage, then %s counters the distracted alchemist for %s damage.',
            ]
        ];

        $context = new CombatContext($attacker, $target, $battle, 'volatile_mixture', 'counter', $actionDef);
        $context->attackerAbilities = collect();
        $context->targetAbilities = collect();

        $this->processor->process($context);

        $this->assertEquals(0, $context->damage, 'No damage to target on backfire');
        $this->assertTrue($context->backfired, 'Should be marked as backfired');
        $this->assertTrue($context->countered, 'Counter flag should be set');
        // Attacker takes both backfire damage AND counter damage
        $this->assertLessThan(0, $context->healing, 'Attacker should take backfire and counter damage');
    }

    public function testRandomRollMechanic()
    {
        $battle = new HeroBattle(); $battle->current_turn = 1;

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->attack = 50;

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;
        $target->defense = 20;

        $actionDef = [
            'attributes' => [
                'success_chance' => 0.8,  // 80% success rate
                'attack_bonus' => 1.5,
            ],
            'messages' => [
                'success' => '%s hurls an unstable concoction, dealing %s damage to %s.',
                'backfire' => '%s\'s volatile mixture explodes prematurely! %s is caught in the blast, taking %s damage.',
            ]
        ];

        // Run multiple times to test randomness
        $successes = 0;
        $backfires = 0;
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            $context = new CombatContext($attacker, $target, $battle, 'volatile_mixture', 'attack', $actionDef);
            $context->attackerAbilities = collect();
            $context->targetAbilities = collect();

            $this->processor->process($context);

            if ($context->backfired) {
                $backfires++;
            } else {
                $successes++;
            }
        }

        // With 80% success rate, expect roughly 80 successes and 20 backfires
        // Allow some variance (60-95 successes is reasonable for 100 iterations)
        $this->assertGreaterThan(60, $successes, 'Should have more successes than backfires with 80% success rate');
        $this->assertLessThan(95, $successes, 'Should have some backfires with 80% success rate');
        $this->assertEquals($iterations, $successes + $backfires, 'All iterations should be accounted for');
    }
}
