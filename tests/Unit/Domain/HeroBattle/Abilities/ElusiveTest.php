<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Elusive;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class ElusiveTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testElusiveSetsEvadeMultiplierToZeroWithoutFocus()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Warrior';
        $attacker->has_focus = false;

        $target = new HeroCombatant();
        $target->name = 'Elusive Rogue';
        $target->current_health = 100;

        $context = new CombatContext($attacker, $target, $battle);
        $context->evadeMultiplier = 1.0;

        $elusive = new Elusive('elusive', []);

        $elusive->beforeDamageReceived($context);

        // Assert: Evade multiplier set to 0 (complete evasion)
        $this->assertEquals(0, $context->evadeMultiplier);
    }

    public function testElusiveDoesNotAffectAttackerWithFocus()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Focused Warrior';
        $attacker->has_focus = true;

        $target = new HeroCombatant();
        $target->name = 'Elusive Rogue';
        $target->current_health = 100;

        $context = new CombatContext($attacker, $target, $battle);
        $context->evadeMultiplier = 1.0;

        $elusive = new Elusive('elusive', []);

        $elusive->beforeDamageReceived($context);

        // Assert: Evade multiplier unchanged when attacker has focus
        $this->assertEquals(1.0, $context->evadeMultiplier);
    }
}
