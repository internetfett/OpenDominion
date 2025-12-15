<?php

namespace Tests\Unit\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Actions\RecoverActionProcessor;
use OpenDominion\Domain\HeroBattle\Combat\CombatCalculator;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use PHPUnit\Framework\TestCase;

class RecoverActionProcessorTest extends TestCase
{
    protected RecoverActionProcessor $processor;
    protected CombatCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CombatCalculator();
        $this->processor = new RecoverActionProcessor($this->calculator, 'recover');
    }

    /**
     * Integration test: Verify healing actually increases health
     */
    public function testRecoverHealsAttacker()
    {
        $battle = new HeroBattle();
        $battle->current_turn = 1;

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->recover = 20;
        $attacker->health = 100;  // Max health
        $attacker->current_health = 50;  // Currently damaged

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;

        $actionDef = [
            'messages' => [
                'recover' => '%s recovers %s health.',
            ]
        ];

        $context = new CombatContext($attacker, $target, $battle, 'recover', 'attack', $actionDef);
        $context->attackerAbilities = collect();
        $context->targetAbilities = collect();

        $this->processor->process($context);

        // Verify healing was set
        $this->assertEquals(20, $context->healing, 'Healing should be set to recover stat');
        $this->assertEquals(0, $context->damage, 'No damage should be dealt');

        // Actually apply the healing
        $context->applyAttackerHealthChange();

        // Verify health was increased
        $this->assertEquals(70, $attacker->current_health, 'Current health should increase by recover amount');
    }

    /**
     * Test that healing doesn't exceed max health
     */
    public function testRecoverCappedAtMaxHealth()
    {
        $battle = new HeroBattle();
        $battle->current_turn = 1;

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->recover = 30;
        $attacker->health = 100;  // Max health
        $attacker->current_health = 85;  // Only 15 HP missing

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;

        $actionDef = [
            'messages' => [
                'recover' => '%s recovers %s health.',
            ]
        ];

        $context = new CombatContext($attacker, $target, $battle, 'recover', 'attack', $actionDef);
        $context->attackerAbilities = collect();
        $context->targetAbilities = collect();

        $this->processor->process($context);

        // Apply the healing
        $context->applyAttackerHealthChange();

        // Verify health was capped at max
        $this->assertEquals(100, $attacker->current_health, 'Health should not exceed max health');
    }

    /**
     * Test that healing works when attacker is at max health
     */
    public function testRecoverAtMaxHealth()
    {
        $battle = new HeroBattle();
        $battle->current_turn = 1;

        $attacker = new HeroCombatant();
        $attacker->hero_battle_id = $battle->id;
        $attacker->recover = 20;
        $attacker->health = 100;
        $attacker->current_health = 100;  // Already at max

        $target = new HeroCombatant();
        $target->hero_battle_id = $battle->id;

        $actionDef = [
            'messages' => [
                'recover' => '%s recovers %s health.',
            ]
        ];

        $context = new CombatContext($attacker, $target, $battle, 'recover', 'attack', $actionDef);
        $context->attackerAbilities = collect();
        $context->targetAbilities = collect();

        $this->processor->process($context);
        $context->applyAttackerHealthChange();

        // Verify health stays at max
        $this->assertEquals(100, $attacker->current_health, 'Health should remain at max');
    }
}
