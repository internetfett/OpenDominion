<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Lifesteal;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class LifestealTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testLifestealHeals50PercentByDefault()
    {
        // Create mock battle
        $battle = new HeroBattle();
        $battle->id = 1;

        // Create attacker with lifesteal
        $attacker = new HeroCombatant();
        $attacker->name = 'Vampire';
        $attacker->current_health = 50;
        $attacker->max_health = 100;

        // Create target
        $target = new HeroCombatant();
        $target->name = 'Hero';
        $target->current_health = 100;
        $target->max_health = 100;

        // Create combat context with 40 damage
        $context = new CombatContext($attacker, $target, $battle);
        $context->damage = 40;

        // Create lifesteal ability with default config
        $lifesteal = new Lifesteal('lifesteal', [
            'heal_percent' => 0.5,
        ]);

        // Trigger lifesteal
        $lifesteal->afterDamageDealt($context);

        // Assert: 50% of 40 damage = 20 healing
        $this->assertEquals(20, $context->healing);
        $this->assertStringContainsString('heals for 20 health', $context->getMessagesString());

        // Health is NOT modified yet - that happens in Phase 2
        $this->assertEquals(50, $attacker->current_health);

        // When context applies healing (Phase 2)
        $context->applyHealing();
        $this->assertEquals(70, $attacker->current_health);
    }

    public function testLifestealHeals100Percent()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Vampire Lord';
        $attacker->current_health = 50;
        $attacker->max_health = 100;

        $target = new HeroCombatant();
        $target->name = 'Hero';
        $target->current_health = 100;
        $target->max_health = 100;

        $context = new CombatContext($attacker, $target, $battle);
        $context->damage = 30;

        // Create lifesteal ability with 100% healing
        $lifesteal = new Lifesteal('lifesteal', [
            'heal_percent' => 1.0,
        ]);

        $lifesteal->afterDamageDealt($context);

        // Assert: 100% of 30 damage = 30 healing
        $this->assertEquals(30, $context->healing);

        // Apply healing
        $context->applyHealing();
        $this->assertEquals(80, $attacker->current_health);
    }

    public function testLifestealDoesNotExceedMaxHealth()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Vampire';
        $attacker->current_health = 95;
        $attacker->max_health = 100;

        $target = new HeroCombatant();
        $target->name = 'Hero';
        $target->current_health = 100;
        $target->max_health = 100;

        $context = new CombatContext($attacker, $target, $battle);
        $context->damage = 40;

        $lifesteal = new Lifesteal('lifesteal', [
            'heal_percent' => 0.5,
        ]);

        $lifesteal->afterDamageDealt($context);

        // Healing is stored in context
        $this->assertEquals(20, $context->healing);

        // When applied, should cap at max health
        $context->applyHealing();
        $this->assertEquals(100, $attacker->current_health);
    }

    public function testLifestealDoesNotTriggerOnZeroDamage()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Vampire';
        $attacker->current_health = 50;
        $attacker->max_health = 100;

        $target = new HeroCombatant();
        $target->name = 'Hero';
        $target->current_health = 100;
        $target->max_health = 100;

        $context = new CombatContext($attacker, $target, $battle);
        $context->damage = 0;

        $lifesteal = new Lifesteal('lifesteal', [
            'heal_percent' => 0.5,
        ]);

        $lifesteal->afterDamageDealt($context);

        // Assert: No healing on 0 damage
        $this->assertEquals(0, $context->healing);
        $this->assertEquals(50, $attacker->current_health);
    }
}
