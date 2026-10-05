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
        $build = $this->factory()->parse('level=9;discipline=riposte;form=feint;gear=greatsword,tower_shield');

        $this->assertEquals(9, $build->level);
        $this->assertEquals('riposte', $build->discipline);
        $this->assertEquals(['feint'], $build->forms);
        $this->assertEquals(['greatsword', 'tower_shield'], $build->gear);
    }

    public function testGearAddsToBaseStats(): void
    {
        $factory = $this->factory();

        $basic = $factory->stats($factory->parse('level=9'));
        $geared = $factory->stats($factory->parse('level=9;gear=greatsword,tower_shield'));

        $this->assertEquals($basic['attack'] + 2, $geared['attack']);
        $this->assertEquals($basic['defense'] + 2, $geared['defense']);
        $this->assertEquals($basic['evasion'] - 10, $geared['evasion']);
    }

    public function testRejectsTheWrongTypeForASlot(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('discipline=feint');
    }

    public function testRejectsPicksAboveTheHerosLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('level=5;form=feint');
    }

    public function testRejectsTwoGearPicksOnOneLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('gear=greatsword,leathers');
    }

    public function testRejectsUnknownOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory()->parse('class=wizard');
    }

    public function testListsOptionsByTypeAndLevel(): void
    {
        $this->assertCount(6, $this->factory()->optionsOfType('discipline'));
        $this->assertCount(6, $this->factory()->optionsOfType('gear', 5));
        $this->assertCount(12, $this->factory()->optionsOfType('gear', 9));
    }

    public function testSimulateCommandReportsAMatchup(): void
    {
        \Illuminate\Support\Facades\Artisan::call('hero-combat:simulate', ['--battles' => 2, '--b' => 'level=9;discipline=riposte']);
        $output = \Illuminate\Support\Facades\Artisan::output();

        $this->assertStringContainsString('A score', $output);
        $this->assertStringContainsString('riposte', $output);
    }

    public function testSimulateCommandRunsAGrid(): void
    {
        \Illuminate\Support\Facades\Artisan::call('hero-combat:simulate', ['--grid' => 'disciplines', '--battles' => 1, '--strategy-a' => 'balanced', '--strategy-b' => 'balanced']);
        $output = \Illuminate\Support\Facades\Artisan::output();

        $this->assertStringContainsString('battle_fury', $output);
        $this->assertStringContainsString('avg', $output);
    }

    public function testSimulateCommandRejectsBadSpecs(): void
    {
        \Illuminate\Support\Facades\Artisan::call('hero-combat:simulate', ['--a' => 'discipline=nope']);

        $this->assertStringContainsString("'nope' is not a discipline", \Illuminate\Support\Facades\Artisan::output());
    }
}
