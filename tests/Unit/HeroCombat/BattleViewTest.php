<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use OpenDominion\HeroCombat\Presentation\BattleView;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Tests\Unit\HeroCombat\Support\BattleBuilder;
use OpenDominion\Tests\Unit\HeroCombat\Support\BuildsBattles;
use PHPUnit\Framework\TestCase;

class BattleViewTest extends TestCase
{
    use BuildsBattles;

    public function testSidesListTheViewersTeamFirstWithTeamNumbers(): void
    {
        $battle = BattleBuilder::make()->npc('Foe', 2)->hero('Player', 1)->build();
        $view = new BattleView(new HeroBattle(), $battle, [$this->named($battle, 'Player')]);

        $sides = $view->sides();

        $this->assertCount(2, $sides);
        $this->assertEquals(1, $sides[0]['team']);
        $this->assertEquals('Your side', $sides[0]['label']);
        $this->assertEquals(2, $sides[1]['team']);
        $this->assertEquals('Opponents', $sides[1]['label']);
        $this->assertEquals(['Foe'], array_map(fn ($c) => $c->name, $sides[1]['combatants']));
    }

    public function testSidesForSpectatorsKeepTeamOrder(): void
    {
        $battle = BattleBuilder::make()->npc('Foe', 2)->hero('Player', 1)->build();
        $view = new BattleView(new HeroBattle(), $battle);

        $this->assertEquals([1, 2], array_column($view->sides(), 'team'));
    }

    public function testSoloHeroCannotQueueAhead(): void
    {
        $battle = BattleBuilder::make()->npc('Foe', 2)->hero('Player', 1)->build();

        $this->assertFalse((new BattleView(new HeroBattle(), $battle, [$this->named($battle, 'Player')]))->canQueueAhead());
    }

    public function testQueueingAheadNeedsAnotherLivingHuman(): void
    {
        $battle = BattleBuilder::make()->npc('Foe', 2)->hero('Player', 1)->hero('Ally', 1)->build();
        $view = new BattleView(new HeroBattle(), $battle, [$this->named($battle, 'Player')]);

        $this->assertTrue($view->canQueueAhead());

        $this->named($battle, 'Ally')->currentHealth = 0;
        $this->assertFalse($view->canQueueAhead(), 'A defeated ally can no longer hold up the turn');
    }

    public function testPilotingEveryLivingHumanCannotQueueAhead(): void
    {
        $battle = BattleBuilder::make()->npc('Foe', 2)->hero('Player', 1)->hero('Ally', 1)->build();
        $view = new BattleView(new HeroBattle(), $battle, [$this->named($battle, 'Player'), $this->named($battle, 'Ally')]);

        $this->assertFalse($view->canQueueAhead(), 'A borrowed ally the viewer pilots cannot hold up the turn');
    }

    public function testPvpOpponentLetsTheViewerQueueAhead(): void
    {
        $battle = BattleBuilder::make()->hero('Player', 1)->hero('Rival', 2)->build();

        $this->assertTrue((new BattleView(new HeroBattle(), $battle, [$this->named($battle, 'Player')]))->canQueueAhead());
    }

    public function testSpectatorsCannotQueue(): void
    {
        $battle = BattleBuilder::make()->hero('Player', 1)->hero('Rival', 2)->build();

        $this->assertFalse((new BattleView(new HeroBattle(), $battle))->canQueueAhead());
    }

    public function testTargetOptionsPutYourselfFirstAndNameTheAllyVerb(): void
    {
        $battle = BattleBuilder::make()->hero('Tank', 1)->hero('Mage', 1)->npc('Boss', 2)->build();
        $tank = $this->named($battle, 'Tank');
        $view = new BattleView(new HeroBattle(), $battle, [$tank]);

        $this->assertEquals(['Yourself', 'Guard Mage'], array_column($view->targetOptions($tank, 'defend'), 'label'));
        $this->assertEquals(['Yourself', 'Heal Mage'], array_column($view->targetOptions($tank, 'recover'), 'label'));
        $this->assertEquals(['ally', 'ally'], array_column($view->targetOptions($tank, 'recover'), 'side'));
        $this->assertSame([], $view->targetOptions($tank, 'attack'), 'One enemy needs no choice');
        $this->assertEquals($this->named($battle, 'Boss')->id, $view->defaultTarget($tank, 'attack'));
        $this->assertEquals(['Yourself', 'Rally Mage'], array_column($view->targetOptions($tank, 'focus'), 'label'));
        $this->assertSame([], $view->targetOptions($tank, 'counter'));
        $this->assertNull($view->defaultTarget($tank, 'focus'));
        $this->assertNull($view->defaultTarget($tank, 'counter'));
    }

    public function testDefendWarnsOnlyWhenRepeatingIt(): void
    {
        $battle = BattleBuilder::make()->hero('Tank', 1)->hero('Mage', 1)->npc('Boss', 2)->build();
        $tank = $this->named($battle, 'Tank');
        $view = new BattleView(new HeroBattle(), $battle, [$tank]);
        $warning = fn () => $battle->registry->ability('defend')->warning($tank);

        $this->assertNull($warning());

        $tank->lastAction = 'guard';
        $this->assertNull($warning());
        $this->assertEquals('Guard', $view->abilityName('guard'));

        $tank->lastAction = 'defend';
        $this->assertEquals('Exposed', $warning());
    }

    public function testOpponentsShowWhatRepeatingAnActionWouldCost(): void
    {
        $battle = BattleBuilder::make()->hero('Player', 1)->hero('Rival', 2)->build();
        $rival = $this->named($battle, 'Rival');
        $view = new BattleView(new HeroBattle(), $battle, [$this->named($battle, 'Player')]);

        $this->assertSame([], $view->warnings($rival));

        $rival->lastAction = 'defend';
        $this->assertCount(1, $view->warnings($rival));
    }

    public function testFocusedHeroCanOnlyRallyTheirAlly(): void
    {
        $battle = BattleBuilder::make()->hero('Tank', 1)->hero('Mage', 1)->npc('Boss', 2)->build();
        $tank = $this->named($battle, 'Tank');
        $battle->effects->apply($tank, 'focused');
        $view = new BattleView(new HeroBattle(), $battle, [$tank]);

        $this->assertSame([], $view->targetOptions($tank, 'focus'), 'One choice needs no picker');
        $this->assertEquals($this->named($battle, 'Mage')->id, $view->defaultTarget($tank, 'focus'));
    }

    public function testSoloHeroHasNoAllyToPick(): void
    {
        $battle = BattleBuilder::make()->hero('Player', 1)->npc('Grunt', 2)->npc('Archer', 2)->build();
        $player = $this->named($battle, 'Player');
        $view = new BattleView(new HeroBattle(), $battle, [$player]);

        $this->assertSame([], $view->targetOptions($player, 'defend'));
        $this->assertNull($view->defaultTarget($player, 'defend'));
        $this->assertEquals(['Grunt', 'Archer'], array_column($view->targetOptions($player, 'attack'), 'label'));
        $this->assertNull($view->defaultTarget($player, 'attack'), 'Two enemies need a choice');
    }
}
