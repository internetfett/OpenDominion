<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Special\Hardiness;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class HardinessTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testHardinessPreventsDeathOnce()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Enemy';

        $target = new HeroCombatant();
        $target->name = 'Hardy Hero';
        $target->current_health = 10;

        $context = new CombatContext($attacker, $target, $battle);
        $context->damage = 50;

        // Create hardiness with 1 charge
        $hardiness = new Hardiness('hardiness', [
            'charges' => 1,
        ]);

        // Trigger before death
        $shouldDie = $hardiness->beforeDeath($context);

        // Assert: Death prevented
        $this->assertFalse($shouldDie);
        $this->assertEquals(1, $target->current_health);
        $this->assertEquals(0, $hardiness->getCharges());
        $this->assertStringContainsString('clings to life with 1 health', $context->getMessagesString());
    }

    public function testHardinessOnlyWorksOnce()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Enemy';

        $target = new HeroCombatant();
        $target->name = 'Hardy Hero';
        $target->current_health = 10;

        $context = new CombatContext($attacker, $target, $battle);
        $context->damage = 50;

        $hardiness = new Hardiness('hardiness', [
            'charges' => 1,
        ]);

        // First death
        $hardiness->beforeDeath($context);
        $this->assertEquals(0, $hardiness->getCharges());

        // Second death - should allow death
        $target->current_health = 1;
        $shouldDie = $hardiness->beforeDeath($context);

        $this->assertTrue($shouldDie);
    }

    public function testHardinessWithNoChargesAllowsDeath()
    {
        $battle = new HeroBattle();
        $battle->id = 1;

        $attacker = new HeroCombatant();
        $attacker->name = 'Enemy';

        $target = new HeroCombatant();
        $target->name = 'Hero';
        $target->current_health = 10;

        $context = new CombatContext($attacker, $target, $battle);

        $hardiness = new Hardiness('hardiness', [
            'charges' => 0,
        ]);

        $shouldDie = $hardiness->beforeDeath($context);

        // Assert: Death allowed when no charges
        $this->assertTrue($shouldDie);
    }
}
