<?php

namespace OpenDominion\Tests\Feature\HeroCombat\Analysis;

use InvalidArgumentException;
use OpenDominion\HeroCombat\Analysis\BuildFactory;
use OpenDominion\Tests\AbstractTestCase;

class BuildFactoryTest extends AbstractTestCase
{
    private function factory(): BuildFactory
    {
        return $this->app->make(BuildFactory::class);
    }

    public function testParsesAFullSpec(): void
    {
        $build = $this->factory()->parse('level=9;technique=backstab,sunder;ability=shadow_strike;gear=greatsword');

        $this->assertEquals(9, $build->level);
        $this->assertEquals(['backstab', 'sunder'], $build->techniques);
        $this->assertEquals('shadow_strike', $build->ability);
        $this->assertEquals(['greatsword'], $build->gear);
    }

    public function testAbilitiesJoinTheActionBarAndPassivesApply(): void
    {
        $factory = $this->factory();
        $battle = new \OpenDominion\HeroCombat\Engine\Battle(
            new \OpenDominion\HeroCombat\Engine\BattleState(null, 1, 1),
            \OpenDominion\Providers\HeroCombatServiceProvider::buildRegistry(),
            new \OpenDominion\Tests\Unit\HeroCombat\Support\ScriptedRandomSource(),
            new \OpenDominion\HeroCombat\Engine\InMemorySpawner(),
        );

        $fortified = $factory->addTo($battle, $factory->parse('technique=sunder;ability=fortify'), 1, 1, 'A', 'balanced');
        $hardy = $factory->addTo($battle, $factory->parse('ability=hardiness'), 2, 2, 'B', 'balanced');

        $this->assertEquals(['attack', 'focus', 'counter', 'recover', 'fortify'], $fortified->abilities);
        $this->assertTrue($battle->effects->has($fortified, 'sunder'));
        $this->assertEquals(['attack', 'defend', 'focus', 'counter', 'recover'], $hardy->abilities);
        $this->assertTrue($battle->effects->has($hardy, 'hardiness'));
    }

    public function testGearAddsToBaseStats(): void
    {
        $factory = $this->factory();

        $basic = $factory->stats($factory->parse('level=9'));
        $geared = $factory->stats($factory->parse('level=9;gear=greatsword'));

        $this->assertEquals($basic['attack'] + 2, $geared['attack']);
        $this->assertEquals($basic['defense'], $geared['defense']);
        $this->assertEquals($basic['evasion'] - 10, $geared['evasion']);
    }

    public function testRejectsTheWrongTypeForASlot(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('ability=feint');
    }

    public function testRejectsRetiredUpgrades(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('technique=overpower');
    }

    public function testBladeFlurryIsNotAHeroPick(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('ability=blade_flurry');
    }

    public function testRejectsTwoTechniquesFromOneTier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('technique=backstab,feint');
    }

    public function testRejectsPicksAboveTheHerosLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('level=5;ability=fortify');
    }

    public function testRejectsTwoGearPicksOnOneLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('gear=greatsword,plate_armor');
    }

    public function testRejectsUnknownOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('class=wizard');
    }

    public function testListsOptionsByTypeAndLevel(): void
    {
        $this->assertCount(11, $this->factory()->optionsOfType('technique'));
        $this->assertCount(6, $this->factory()->optionsOfType('technique', level: 3));
        $this->assertCount(5, $this->factory()->optionsOfType('technique', level: 5));
        $this->assertCount(10, $this->factory()->optionsOfType('ability'));
        $this->assertCount(0, $this->factory()->optionsOfType('gear', 7));
        $this->assertCount(6, $this->factory()->optionsOfType('gear', 9));
    }

    public function testSimulateCommandReportsAMatchup(): void
    {
        \Illuminate\Support\Facades\Artisan::call('hero-combat:simulate', ['--battles' => 2, '--b' => 'level=9;technique=backstab']);
        $output = \Illuminate\Support\Facades\Artisan::output();

        $this->assertStringContainsString('A score', $output);
        $this->assertStringContainsString('backstab', $output);
    }

    public function testSimulateCommandRunsAGrid(): void
    {
        \Illuminate\Support\Facades\Artisan::call('hero-combat:simulate', ['--grid' => 'abilities', '--battles' => 1, '--strategy-a' => 'balanced', '--strategy-b' => 'balanced']);
        $output = \Illuminate\Support\Facades\Artisan::output();

        $this->assertStringContainsString('shadow_strike', $output);
        $this->assertStringContainsString('avg', $output);
    }

    public function testSimulateCommandRejectsBadSpecs(): void
    {
        \Illuminate\Support\Facades\Artisan::call('hero-combat:simulate', ['--a' => 'technique=nope']);

        $this->assertStringContainsString("'nope' is not a technique", \Illuminate\Support\Facades\Artisan::output());
    }
}
