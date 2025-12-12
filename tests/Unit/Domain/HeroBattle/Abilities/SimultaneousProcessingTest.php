<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Lifesteal;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class SimultaneousProcessingTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testSimultaneousDamageProcessing()
    {
        // Setup: Two players attacking each other
        $battle = new HeroBattle();
        $battle->id = 1;
        $battle->current_turn = 1;

        $playerA = new HeroCombatant();
        $playerA->name = 'Player A';
        $playerA->current_health = 100;
        $playerA->max_health = 100;

        $playerB = new HeroCombatant();
        $playerB->name = 'Player B';
        $playerB->current_health = 100;
        $playerB->max_health = 100;

        // PHASE 1: Calculate damage for both attacks
        $contextA = new CombatContext($playerA, $playerB, $battle);
        $contextA->damage = 30; // A deals 30 to B

        $contextB = new CombatContext($playerB, $playerA, $battle);
        $contextB->damage = 25; // B deals 25 to A

        // Abilities modify context values (not health directly)
        $lifesteal = new Lifesteal('lifesteal', ['heal_percent' => 0.5]);
        $lifesteal->afterDamageDealt($contextA); // A heals for 15

        // At this point, NO health has changed
        $this->assertEquals(100, $playerA->current_health);
        $this->assertEquals(100, $playerB->current_health);
        $this->assertEquals(15, $contextA->healing);

        // PHASE 2: Apply ALL damage/healing simultaneously
        // Apply all damage first, then all healing (order matters for max health clamping)
        $contextA->applyDamage();
        $contextB->applyDamage();
        $contextA->applyHealing();
        $contextB->applyHealing();

        // Final state: Both attacks happened "at the same time"
        // Player A: 100 - 25 + 15 = 90 (took 25, healed 15)
        // Player B: 100 - 30 = 70 (took 30)
        $this->assertEquals(90, $playerA->current_health);
        $this->assertEquals(70, $playerB->current_health);
    }

    public function testSimultaneousProcessingPreventsUnfairAdvantage()
    {
        // This test demonstrates why simultaneous processing matters
        $battle = new HeroBattle();
        $battle->id = 1;

        $vampireA = new HeroCombatant();
        $vampireA->name = 'Vampire A';
        $vampireA->current_health = 50;
        $vampireA->max_health = 100;

        $vampireB = new HeroCombatant();
        $vampireB->name = 'Vampire B';
        $vampireB->current_health = 50;
        $vampireB->max_health = 100;

        // Both vampires attack each other with lifesteal
        $contextA = new CombatContext($vampireA, $vampireB, $battle);
        $contextA->damage = 40;

        $contextB = new CombatContext($vampireB, $vampireA, $battle);
        $contextB->damage = 40;

        $lifestealA = new Lifesteal('lifesteal', ['heal_percent' => 0.5]);
        $lifestealB = new Lifesteal('lifesteal', ['heal_percent' => 0.5]);

        $lifestealA->afterDamageDealt($contextA);
        $lifestealB->afterDamageDealt($contextB);

        // Both calculated healing before any health changes
        $this->assertEquals(20, $contextA->healing);
        $this->assertEquals(20, $contextB->healing);

        // Apply simultaneously (damage first, then healing)
        $contextA->applyDamage();
        $contextB->applyDamage();
        $contextA->applyHealing();
        $contextB->applyHealing();

        // Both end up at the same health (fair)
        // 50 - 40 + 20 = 30
        $this->assertEquals(30, $vampireA->current_health);
        $this->assertEquals(30, $vampireB->current_health);

        // If this was sequential, first vampire would have unfair advantage:
        // Vampire A: 50 - 0 + 20 = 70 (heals before being hit)
        // Vampire B: 50 - 40 + 20 = 30 (gets hit before healing)
    }

    public function testDamageAppliedToShieldFirst()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Attacker';

        $target = new HeroCombatant();
        $target->name = 'Shielded Target';
        $target->current_health = 100;
        $target->max_health = 100;
        $target->shield = 30;

        $context = new CombatContext($attacker, $target, $battle);
        $context->damage = 50;

        // Apply damage
        $context->applyDamage();

        // Shield absorbs 30, health takes remaining 20
        $this->assertEquals(0, $target->shield);
        $this->assertEquals(80, $target->current_health);
    }

    public function testDamageFullyAbsorbedByShield()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Attacker';

        $target = new HeroCombatant();
        $target->name = 'Shielded Target';
        $target->current_health = 100;
        $target->max_health = 100;
        $target->shield = 50;

        $context = new CombatContext($attacker, $target, $battle);
        $context->damage = 30;

        $context->applyDamage();

        // Shield absorbs all damage
        $this->assertEquals(20, $target->shield);
        $this->assertEquals(100, $target->current_health);
    }
}
