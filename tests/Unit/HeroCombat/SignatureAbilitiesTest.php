<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use OpenDominion\HeroCombat\Engine\ActionValidator;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\Tests\Unit\HeroCombat\Support\BattleBuilder;
use OpenDominion\Tests\Unit\HeroCombat\Support\BuildsBattles;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Fortify, Caltrops, and Exposed as Recover's penalty and alongside Sunder.
 */
class SignatureAbilitiesTest extends TestCase
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

    private function channeler(bool $firstTeamFirst = true, string $ability = 'attack'): Battle
    {
        $battle = $this->teams($ability, $firstTeamFirst);
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'channeling');
        $battle->effects->apply($hero, 'focused');

        return $battle;
    }

    #[DataProvider('teamOrders')]
    public function testAChanneledAttackExposesTheTargetForTheWholeTurn(bool $firstTeamFirst): void
    {
        $battle = $this->channeler($firstTeamFirst);

        $this->turn($battle, ['Hero' => ['attack', 'Foe'], 'Ally' => ['attack', 'Foe'], 'Foe' => ['defend'], 'Other' => ['focus']]);

        $this->assertEquals(100 - (50 - 35) - (40 - 35), $this->health($battle, 'Foe'), 'Defending and Exposed: 20 x (2 - 0.25), for the ally too');
        $this->assertFalse($battle->effects->has($this->named($battle, 'Hero'), 'focused'));
    }

    public function testAnUnfocusedAttackExposesNothing(): void
    {
        $battle = $this->teams('attack');
        $battle->effects->apply($this->named($battle, 'Hero'), 'channeling');

        $this->turn($battle, ['Hero' => ['attack', 'Foe'], 'Ally' => ['focus'], 'Foe' => ['defend'], 'Other' => ['focus']]);

        $this->assertEquals(100, $this->health($battle, 'Foe'));
    }

    #[DataProvider('teamOrders')]
    public function testAGuardTakingTheChanneledHitIsNotExposed(bool $firstTeamFirst): void
    {
        $battle = $this->channeler($firstTeamFirst);

        $this->turn($battle, ['Hero' => ['attack', 'Foe'], 'Ally' => ['attack', 'Foe'], 'Foe' => ['focus'], 'Other' => ['defend', 'Foe']]);

        $this->assertEquals(100 - (50 - 30) - (40 - 30), $this->health($battle, 'Other'), 'The guard keeps its 30 defense');
        $this->assertEquals(100, $this->health($battle, 'Foe'));
    }

    /**
     * Foe Defends (40 defense); unchanneled, the ally's 40 does nothing.
     *
     * @return array<string, array{0: string, 1: string|null, 2: int}>
     */
    public static function otherAttacks(): array
    {
        return [
            'Blade Flurry' => ['blade_flurry', 'Foe', 100 - 2 * (43 - 40)],
            'Whirlwind' => ['whirlwind', null, 100 - 7],
        ];
    }

    #[DataProvider('otherAttacks')]
    public function testOnlyTheBasicAttackIsChanneled(string $ability, ?string $target, int $foeHealth): void
    {
        $battle = $this->channeler(ability: $ability);

        $this->turn($battle, ['Hero' => [$ability, $target], 'Ally' => ['attack', 'Foe'], 'Foe' => ['defend'], 'Other' => ['focus']]);

        $this->assertEquals($foeHealth, $this->health($battle, 'Foe'), 'Not Exposed');
        $this->assertFalse($battle->effects->has($this->named($battle, 'Hero'), 'focused'), 'The Focus is still spent');
    }

    private function hardy(int $health, bool $firstTeamFirst = true): Battle
    {
        $battle = $this->teams('attack', $firstTeamFirst);
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'hardiness');
        $hero->currentHealth = $health;

        return $battle;
    }

    public function testHardinessAddsFiveDefenseAtFortyHealthOrLess(): void
    {
        $battle = $this->hardy(40);
        $this->turn($battle, ['Hero' => ['focus'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Hero'], 'Other' => ['focus']]);
        $this->assertEquals(40 - (40 - 25), $this->health($battle, 'Hero'));

        $battle = $this->hardy(41);
        $this->turn($battle, ['Hero' => ['focus'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Hero'], 'Other' => ['focus']]);
        $this->assertEquals(41 - (40 - 20), $this->health($battle, 'Hero'), 'Above 40 health');
    }

    public function testHardinessDoesNothingWhileDefendingOrGuarding(): void
    {
        $battle = $this->hardy(40);
        $this->turn($battle, ['Hero' => ['defend', 'Ally'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Ally'], 'Other' => ['focus']]);
        $this->assertEquals(40 - (40 - 30), $this->health($battle, 'Hero'), 'Guarding: 30, not 35');

        $battle = $this->hardy(40);
        $battle->effects->apply($this->named($battle, 'Foe'), 'focused');
        $this->turn($battle, ['Hero' => ['defend'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Hero'], 'Other' => ['focus']]);
        $this->assertEquals(40 - (50 - 40), $this->health($battle, 'Hero'), 'Defending: 40, not 45');
    }

    #[DataProvider('teamOrders')]
    public function testHardinessUsesHealthFromTheStartOfTheTurn(bool $firstTeamFirst): void
    {
        $battle = $this->hardy(45, $firstTeamFirst);

        $this->turn($battle, ['Hero' => ['focus'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Hero'], 'Other' => ['attack', 'Hero']]);

        $this->assertEquals(45 - 20 - 20, $this->health($battle, 'Hero'), 'The first hit doesn\'t switch it on for the second');
    }

    public function testFortifyAddsTenDefenseFromTheTurnItIsCast(): void
    {
        $battle = $this->teams('fortify');

        $this->turn($battle, ['Hero' => ['fortify'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Hero'], 'Other' => ['focus']]);

        $this->assertEquals(90, $this->health($battle, 'Hero'), '40 against 30');
    }

    public function testFortifyCanBeCastOnAnAlly(): void
    {
        $battle = $this->teams('fortify');

        $this->turn($battle, ['Hero' => ['fortify', 'Ally'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Ally'], 'Other' => ['focus']]);

        $this->assertEquals(90, $this->health($battle, 'Ally'));
        $this->assertTrue($battle->effects->has($this->named($battle, 'Ally'), 'fortified'));
    }

    public function testFortifyDoesNothingWhileDefendingOrGuarding(): void
    {
        $battle = $this->teams('fortify');
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'fortified');
        $this->declare($battle, 'Hero', 'defend');

        $this->assertEquals(40, $battle->stat($hero, Stat::Defense), 'Never stacks with a stance');
    }

    public function testFortifyLastsThreeTurnsAndARecastStartsThemOver(): void
    {
        $battle = $this->teams('fortify');
        $hero = $this->named($battle, 'Hero');
        $idle = ['Ally' => ['focus'], 'Foe' => ['focus'], 'Other' => ['focus']];

        $this->turn($battle, ['Hero' => ['fortify']] + $idle);
        $this->assertFalse((new ActionValidator($battle))->canPerform($hero, 'fortify'), 'Not twice in a row');
        $this->turn($battle, ['Hero' => ['counter']] + $idle);
        $this->turn($battle, ['Hero' => ['fortify']] + $idle);
        $this->turn($battle, ['Hero' => ['counter']] + $idle);
        $this->assertTrue($battle->effects->has($hero, 'fortified'), 'The recast on turn 3 covers turns 3 to 5');

        $this->turn($battle, ['Hero' => ['focus']] + $idle);
        $this->assertFalse($battle->effects->has($hero, 'fortified'), 'Gone after turn 5');
    }

    #[DataProvider('teamOrders')]
    public function testCaltropsHitEveryAttackerThatTurnThenAreGone(bool $firstTeamFirst): void
    {
        $battle = $this->teams('caltrops', $firstTeamFirst);

        $this->turn($battle, ['Hero' => ['caltrops'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Hero'], 'Other' => ['attack', 'Hero']]);

        $this->assertEquals(85, $this->health($battle, 'Foe'));
        $this->assertEquals(85, $this->health($battle, 'Other'));
        $this->assertFalse($battle->effects->has($this->named($battle, 'Hero'), 'caltrops_laid'));
    }

    public function testCaltropsWaitUntilSomeoneAttacks(): void
    {
        $battle = $this->teams('caltrops');

        $this->turn($battle, ['Hero' => ['caltrops', 'Ally'], 'Ally' => ['focus'], 'Foe' => ['focus'], 'Other' => ['focus']]);
        $this->assertTrue($battle->effects->has($this->named($battle, 'Ally'), 'caltrops_laid'));

        $this->turn($battle, ['Hero' => ['focus'], 'Ally' => ['counter'], 'Foe' => ['attack', 'Ally'], 'Other' => ['defend']]);

        $this->assertEquals(100 - 15 - 30, $this->health($battle, 'Foe'), 'Caltrops 15, then the Counter 30');
    }

    public function testCaltropsDamageIgnoresDefenseAndCannotBeTakenByAGuard(): void
    {
        $battle = $this->teams('caltrops');

        $this->turn($battle, ['Hero' => ['caltrops'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Hero'], 'Other' => ['defend', 'Foe']]);

        $this->assertEquals(85, $this->health($battle, 'Foe'));
        $this->assertEquals(100, $this->health($battle, 'Other'));
    }

    public function testAGuardKeepsTheCaltropsFromSpringing(): void
    {
        $battle = $this->teams('caltrops');

        $this->turn($battle, ['Hero' => ['caltrops', 'Ally'], 'Ally' => ['focus'], 'Foe' => ['attack', 'Ally'], 'Other' => ['focus']]);
        $this->assertEquals(85, $this->health($battle, 'Foe'), 'Sprung');

        $battle = $this->teams('caltrops');
        $this->turn($battle, ['Hero' => ['caltrops', 'Ally'], 'Ally' => ['focus'], 'Foe' => ['focus'], 'Other' => ['focus']]);
        $this->turn($battle, ['Hero' => ['defend', 'Ally'], 'Ally' => ['defend'], 'Foe' => ['attack', 'Ally'], 'Other' => ['defend']]);

        $this->assertEquals(100, $this->health($battle, 'Foe'), 'The guard took the hit, so the caltrops wait');
        $this->assertTrue($battle->effects->has($this->named($battle, 'Ally'), 'caltrops_laid'));
    }

    public function testNoLoadoutAbilityIsAlsoAnEffect(): void
    {
        $registry = \OpenDominion\Providers\HeroCombatServiceProvider::buildRegistry();

        foreach ($registry->loadoutOptions() as $option) {
            if ($registry->hasAbility($option->key)) {
                $this->assertFalse($registry->hasEffect($option->key), "{$option->key} would also be applied as a passive when a hero spawns");
            }
        }
    }

    public function testRecoverLeavesTheHealerExposed(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->npc('Foe')->build();
        $hero = $this->named($battle, 'Hero');
        $hero->currentHealth = 50;

        $this->declare($battle, 'Hero', 'recover');

        $this->assertEquals(15, $battle->stat($hero, Stat::Defense));
    }

    public function testSunderStacksWithASelfInflictedExposed(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->npc('Foe')->build();
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'exposed', 2, 1, ['sunder' => true]);
        $hero->lastAction = 'defend';

        $this->declare($battle, 'Hero', 'defend');

        $this->assertEquals(30, $battle->stat($hero, Stat::Defense), '20 x (2 - 0.25 - 0.25)');
    }
}
