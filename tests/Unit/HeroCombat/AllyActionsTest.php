<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\Tests\Unit\HeroCombat\Support\BattleBuilder;
use OpenDominion\Tests\Unit\HeroCombat\Support\BuildsBattles;
use PHPUnit\Framework\TestCase;

class AllyActionsTest extends TestCase
{
    use BuildsBattles;

    private function party(): Battle
    {
        return BattleBuilder::make()
            ->hero('Tank', team: 1)
            ->hero('Mage', team: 1)
            ->npc('Boss', team: 2, stats: ['attack' => 60, 'health' => 500])
            ->build();
    }

    public function testDefendWithoutTargetStillDefendsSelf(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->npc('Foe')->build();
        $this->queue($battle, 'Hero', 'defend');

        $this->resolveTurn($battle);

        $this->assertEquals(100, $this->named($battle, 'Hero')->currentHealth);
        $this->assertStringContainsString('takes a defensive stance', $battle->log->text());
    }

    /**
     * The boss is a queued "hero" so it can be told exactly whom to hit.
     */
    private function partyWithControlledBoss(): Battle
    {
        return BattleBuilder::make()
            ->hero('Tank', team: 1)
            ->hero('Mage', team: 1)
            ->hero('Boss', team: 2, stats: ['attack' => 60, 'health' => 500])
            ->build();
    }

    public function testGuardRedirectsHitsToTheGuardianWithDoubledDefense(): void
    {
        $battle = $this->partyWithControlledBoss();
        $this->queue($battle, 'Tank', 'defend', 'Mage');
        $this->queue($battle, 'Mage', 'attack', 'Boss');
        $this->queue($battle, 'Boss', 'attack', 'Mage');

        $this->resolveTurn($battle);

        $this->assertEquals(100, $this->named($battle, 'Mage')->currentHealth);
        $this->assertEquals(100 - (60 - 40), $this->named($battle, 'Tank')->currentHealth);
        $this->assertStringContainsString('stands guard over Mage', $battle->log->text());
        $this->assertStringContainsString('Tank steps in front of Mage', $battle->log->text());
    }

    public function testGuardOnlyLastsOneTurn(): void
    {
        $battle = $this->partyWithControlledBoss();
        $this->queue($battle, 'Tank', 'defend', 'Mage');
        $this->queue($battle, 'Mage', 'focus');
        $this->queue($battle, 'Boss', 'focus');
        $this->resolveTurn($battle);

        $this->queue($battle, 'Tank', 'attack', 'Boss');
        $this->queue($battle, 'Mage', 'attack', 'Boss');
        $this->queue($battle, 'Boss', 'attack', 'Mage');
        $this->resolveTurn($battle);

        $this->assertEquals(100 - (60 + 10 - 20), $this->named($battle, 'Mage')->currentHealth);
    }

    public function testMutualGuardDoesNotLoop(): void
    {
        $battle = $this->party();
        $this->queue($battle, 'Tank', 'defend', 'Mage');
        $this->queue($battle, 'Mage', 'defend', 'Tank');

        $this->resolveTurn($battle);

        $this->assertTrue($this->named($battle, 'Tank')->isAlive());
        $this->assertTrue($this->named($battle, 'Mage')->isAlive());
    }

    public function testRecoverHealsAnAllyAndKeepsThePenaltyOnTheCaster(): void
    {
        $battle = $this->party();
        $mage = $this->named($battle, 'Mage');
        $tank = $this->named($battle, 'Tank');
        $mage->currentHealth = 50;

        $this->declare($battle, 'Tank', 'recover');
        $text = $this->perform($battle, 'Tank', 'recover', 'Mage');

        $this->assertEquals(70, $mage->currentHealth);
        $this->assertEquals(15, $battle->stat($tank, Stat::Defense));
        $this->assertEquals(20, $battle->stat($mage, Stat::Defense));
        $this->assertStringContainsString('tends to Mage', $text);
    }

    public function testCannotTargetAnEnemyWithAllyAbilities(): void
    {
        $battle = $this->party();

        $this->assertFalse($battle->targets->isValidChoice($this->named($battle, 'Tank'), $battle->registry->ability('recover'), $this->named($battle, 'Boss')));
        $this->assertTrue($battle->targets->isValidChoice($this->named($battle, 'Tank'), $battle->registry->ability('recover'), null));
    }

    public function testAiHealsTheMostWoundedAllyAndGuardsOnlyWhenNeeded(): void
    {
        $battle = $this->party();
        $tank = $this->named($battle, 'Tank');
        $mage = $this->named($battle, 'Mage');
        $strategy = new class('test', 'Test', []) extends \OpenDominion\HeroCombat\Content\Ai\WeightedStrategy {
            public function target(\OpenDominion\HeroCombat\Engine\CombatantState $actor, string $ability, Battle $battle): ?\OpenDominion\HeroCombat\Engine\CombatantState
            {
                return $this->chooseTarget($actor, $battle->registry->ability($ability), $battle);
            }
        };

        $mage->currentHealth = 60;
        $this->assertSame($mage, $strategy->target($tank, 'recover', $battle));
        $this->assertSame($tank, $strategy->target($tank, 'defend', $battle));

        $mage->currentHealth = 30;
        $this->assertSame($mage, $strategy->target($tank, 'defend', $battle));
    }
}
