<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use OpenDominion\HeroCombat\Engine\ActionValidator;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\Tests\Unit\HeroCombat\Support\BattleBuilder;
use OpenDominion\Tests\Unit\HeroCombat\Support\BuildsBattles;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Blade Flurry and Whirlwind, the abilities open to every class. Base stats are 40 attack,
 * 20 defense, 10 focus and 10 counter.
 */
class UniversalAbilitiesTest extends TestCase
{
    use BuildsBattles;

    private const BASICS = ['attack', 'defend', 'focus', 'counter', 'recover'];

    /**
     * @return array<string, array{0: bool}>
     */
    public static function teamOrders(): array
    {
        return ['first team acts first' => [true], 'second team acts first' => [false]];
    }

    private function teams(string $ability, bool $firstTeamFirst = true): Battle
    {
        $builder = BattleBuilder::make();
        $abilities = [...self::BASICS, $ability];

        return $firstTeamFirst
            ? $builder->hero('Hero', abilities: $abilities)->hero('Ally')->hero('Foe', team: 2)->hero('Other', team: 2)->build()
            : $builder->hero('Foe', team: 2)->hero('Other', team: 2)->hero('Hero', abilities: $abilities)->hero('Ally')->build();
    }

    /**
     * @param array<string, array{0: string, 1?: string}> $orders
     */
    private function turn(Battle $battle, array $orders): void
    {
        foreach ($orders as $name => $order) {
            $this->queue($battle, $name, $order[0], $order[1] ?? null);
        }
        $this->resolveTurn($battle);
    }

    private function health(Battle $battle, string $name): int
    {
        return $this->named($battle, $name)->currentHealth;
    }

    public function testBladeFlurryScalesTheAttackValueBeforeDefense(): void
    {
        $battle = $this->teams('blade_flurry');

        $this->turn($battle, ['Hero' => ['blade_flurry', 'Foe'], 'Ally' => ['focus'], 'Foe' => ['focus'], 'Other' => ['focus']]);

        $this->assertEquals(100 - 2 * (34 - 20), $this->health($battle, 'Foe'), '85% of 40 is 34, minus 20 defense, twice');
    }

    public function testBladeFlurryScalesFocusAlongWithTheAttack(): void
    {
        $battle = $this->teams('blade_flurry');
        $battle->effects->apply($this->named($battle, 'Hero'), 'focused');

        $this->turn($battle, ['Hero' => ['blade_flurry', 'Foe'], 'Ally' => ['focus'], 'Foe' => ['focus'], 'Other' => ['focus']]);

        $this->assertEquals(100 - 2 * (43 - 20), $this->health($battle, 'Foe'), '85% of 50 rounds to 43');
    }

    #[DataProvider('teamOrders')]
    public function testBladeFlurryScalesRallyAlongWithTheAttack(bool $firstTeamFirst): void
    {
        $battle = $this->teams('blade_flurry', $firstTeamFirst);

        $this->turn($battle, ['Hero' => ['blade_flurry', 'Foe'], 'Ally' => ['focus', 'Hero'], 'Foe' => ['focus'], 'Other' => ['focus']]);

        $this->assertEquals(100 - 2 * (43 - 20), $this->health($battle, 'Foe'));
    }

    public function testBladeFlurryDoesNothingAgainstADefender(): void
    {
        $battle = $this->teams('blade_flurry');

        $this->turn($battle, ['Hero' => ['blade_flurry', 'Foe'], 'Ally' => ['focus'], 'Foe' => ['defend'], 'Other' => ['focus']]);

        $this->assertEquals(100, $this->health($battle, 'Foe'), '34 against 40 defense');
    }

    #[DataProvider('teamOrders')]
    public function testACounterStrikesBackOnceForEachFlurryHit(bool $firstTeamFirst): void
    {
        $battle = $this->teams('blade_flurry', $firstTeamFirst);

        $this->turn($battle, ['Hero' => ['blade_flurry', 'Foe'], 'Ally' => ['focus'], 'Foe' => ['counter'], 'Other' => ['focus']]);

        $this->assertEquals(100 - 2 * 30, $this->health($battle, 'Hero'), '40 + 10 counter - 20 defense, twice');
        $this->assertEquals(72, $this->health($battle, 'Foe'));
    }

    public function testBladeFlurryCannotBeUsedTwiceInARow(): void
    {
        $battle = $this->teams('blade_flurry');

        $this->turn($battle, ['Hero' => ['blade_flurry', 'Foe'], 'Ally' => ['focus'], 'Foe' => ['focus'], 'Other' => ['focus']]);

        $this->assertFalse((new ActionValidator($battle))->canPerform($this->named($battle, 'Hero'), 'blade_flurry'));
    }

    #[DataProvider('teamOrders')]
    public function testWhirlwindHitsEveryEnemyForSeventyPercent(bool $firstTeamFirst): void
    {
        $battle = $this->teams('whirlwind', $firstTeamFirst);

        $this->turn($battle, ['Hero' => ['whirlwind'], 'Ally' => ['focus'], 'Foe' => ['focus'], 'Other' => ['focus']]);

        $this->assertEquals(86, $this->health($battle, 'Foe'), '70% of 40 - 20');
        $this->assertEquals(86, $this->health($battle, 'Other'));
        $this->assertEquals(100, $this->health($battle, 'Ally'));
    }

    #[DataProvider('teamOrders')]
    public function testEveryEnemyWhirlwindHitsCanCounter(bool $firstTeamFirst): void
    {
        $battle = $this->teams('whirlwind', $firstTeamFirst);

        $this->turn($battle, ['Hero' => ['whirlwind'], 'Ally' => ['focus'], 'Foe' => ['counter'], 'Other' => ['counter']]);

        $this->assertEquals(100 - 2 * 30, $this->health($battle, 'Hero'));
    }

    #[DataProvider('teamOrders')]
    public function testAGuardTakesBothWhirlwindHits(bool $firstTeamFirst): void
    {
        $battle = $this->teams('whirlwind', $firstTeamFirst);

        $this->turn($battle, ['Hero' => ['whirlwind'], 'Ally' => ['focus'], 'Foe' => ['defend', 'Other'], 'Other' => ['focus']]);

        $this->assertEquals(100 - 2 * 7, $this->health($battle, 'Foe'), '70% of 40 - 30 guarding defense, twice');
        $this->assertEquals(100, $this->health($battle, 'Other'));
    }

    public function testFocusAddsToEveryWhirlwindHitAndIsSpentOnce(): void
    {
        $battle = $this->teams('whirlwind');
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'focused');

        $this->turn($battle, ['Hero' => ['whirlwind'], 'Ally' => ['focus'], 'Foe' => ['focus'], 'Other' => ['focus']]);

        $this->assertEquals(79, $this->health($battle, 'Foe'), '70% of 50 - 20');
        $this->assertEquals(79, $this->health($battle, 'Other'));
        $this->assertFalse($battle->effects->has($hero, 'focused'));
    }

    public function testWhirlwindCannotBeUsedTwiceInARow(): void
    {
        $battle = $this->teams('whirlwind');

        $this->turn($battle, ['Hero' => ['whirlwind'], 'Ally' => ['focus'], 'Foe' => ['focus'], 'Other' => ['focus']]);

        $this->assertFalse((new ActionValidator($battle))->canPerform($this->named($battle, 'Hero'), 'whirlwind'));
    }
}
