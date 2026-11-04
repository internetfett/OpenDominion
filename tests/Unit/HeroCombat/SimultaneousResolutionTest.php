<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use OpenDominion\HeroCombat\Content\Abilities\Boss\Bloodrend;
use OpenDominion\HeroCombat\Content\Loadouts\HeroClassLoadouts;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Events\BattleEvent;
use OpenDominion\HeroCombat\Engine\Events\EventType;
use OpenDominion\Tests\Unit\HeroCombat\Support\BattleBuilder;
use OpenDominion\Tests\Unit\HeroCombat\Support\BuildsBattles;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Damage and healing land together: the order of combatant ids never decides who lives.
 */
class SimultaneousResolutionTest extends TestCase
{
    use BuildsBattles;

    /**
     * @return array<string, array{0: bool}>
     */
    public static function teamOrders(): array
    {
        return ['first team acts first' => [true], 'second team acts first' => [false]];
    }

    private function teams(bool $firstTeamFirst): Battle
    {
        $builder = BattleBuilder::make();

        return $firstTeamFirst
            ? $builder->hero('Hero')->hero('Ally')->hero('Foe', team: 2)->hero('Other', team: 2)->build()
            : $builder->hero('Foe', team: 2)->hero('Other', team: 2)->hero('Hero')->hero('Ally')->build();
    }

    #[DataProvider('teamOrders')]
    public function testSameTurnHealSavesTheTargetWhateverTheIdOrder(bool $firstTeamFirst): void
    {
        $battle = $this->teams($firstTeamFirst);
        $this->named($battle, 'Hero')->currentHealth = 10;
        $this->queue($battle, 'Foe', 'attack', 'Hero');
        $this->queue($battle, 'Other', 'defend');
        $this->queue($battle, 'Hero', 'focus');
        $this->queue($battle, 'Ally', 'recover', 'Hero');

        $this->resolveTurn($battle);

        $this->assertTrue($this->named($battle, 'Hero')->isAlive());
        $this->assertEquals(10, $this->named($battle, 'Hero')->currentHealth);
    }

    #[DataProvider('teamOrders')]
    public function testInsufficientHealStillLosesTheHero(bool $firstTeamFirst): void
    {
        $battle = $this->teams($firstTeamFirst);
        $this->named($battle, 'Hero')->currentHealth = 10;
        $this->queue($battle, 'Foe', 'attack', 'Hero');
        $this->queue($battle, 'Other', 'attack', 'Hero');
        $this->queue($battle, 'Hero', 'focus');
        $this->queue($battle, 'Ally', 'recover', 'Hero');

        $this->resolveTurn($battle);

        $this->assertFalse($this->named($battle, 'Hero')->isAlive());
    }

    public function testHealAtFullHealthOffsetsTheSameTurnsDamage(): void
    {
        $battle = $this->teams(true);
        $this->queue($battle, 'Foe', 'attack', 'Hero');
        $this->queue($battle, 'Other', 'defend');
        $this->queue($battle, 'Hero', 'focus');
        $this->queue($battle, 'Ally', 'recover', 'Hero');

        $this->resolveTurn($battle);

        $this->assertEquals(100, $this->named($battle, 'Hero')->currentHealth);
        $heal = $this->lastEvent($battle, EventType::Heal);
        $this->assertEquals(20, $heal->toArray()['amount']);
    }

    #[DataProvider('teamOrders')]
    public function testAttackOnAFallingTargetIsNotRetargeted(bool $firstTeamFirst): void
    {
        $battle = $this->teams($firstTeamFirst);
        $this->named($battle, 'Foe')->currentHealth = 10;
        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->queue($battle, 'Ally', 'attack', 'Foe');
        $this->queue($battle, 'Foe', 'focus');
        $this->queue($battle, 'Other', 'focus');

        $this->resolveTurn($battle);

        $this->assertFalse($this->named($battle, 'Foe')->isAlive());
        $this->assertEquals(100, $this->named($battle, 'Other')->currentHealth);
    }

    public function testBothSidesCanFallOnTheSameTurn(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->hero('Foe', team: 2)->build();
        $this->named($battle, 'Hero')->currentHealth = 10;
        $this->named($battle, 'Foe')->currentHealth = 10;
        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->queue($battle, 'Foe', 'attack', 'Hero');

        $this->resolveTurn($battle);

        $this->assertTrue($battle->state->finished);
        $this->assertNull($battle->state->winningTeam);
    }

    #[DataProvider('teamOrders')]
    public function testFallingHeroStillCounters(bool $firstTeamFirst): void
    {
        $battle = $this->teams($firstTeamFirst);
        $this->named($battle, 'Hero')->currentHealth = 10;
        $this->queue($battle, 'Foe', 'attack', 'Hero');
        $this->queue($battle, 'Other', 'defend');
        $this->queue($battle, 'Hero', 'counter');
        $this->queue($battle, 'Ally', 'defend');

        $this->resolveTurn($battle);

        $this->assertFalse($this->named($battle, 'Hero')->isAlive());
        $this->assertEquals(70, $this->named($battle, 'Foe')->currentHealth);
    }

    public function testHardinessSavesOnceAfterTheWholeTurnsDamage(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->hero('Foe', team: 2)->hero('Other', team: 2)->build();
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'cling_to_life');
        $hero->currentHealth = 30;
        $this->queue($battle, 'Foe', 'attack', 'Hero');
        $this->queue($battle, 'Other', 'attack', 'Hero');
        $this->queue($battle, 'Hero', 'focus');

        $this->resolveTurn($battle);

        $this->assertEquals(1, $hero->currentHealth);
        $this->assertFalse($battle->effects->has($hero, 'cling_to_life'));
    }

    public function testHardinessIsKeptWhenHealingPreventsTheDefeat(): void
    {
        $battle = BattleBuilder::make()->hero('Foe', team: 2)->hero('Hero')->hero('Ally')->build();
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'cling_to_life');
        $hero->currentHealth = 10;
        $this->queue($battle, 'Foe', 'attack', 'Hero');
        $this->queue($battle, 'Hero', 'focus');
        $this->queue($battle, 'Ally', 'recover', 'Hero');

        $this->resolveTurn($battle);

        $this->assertEquals(10, $hero->currentHealth);
        $this->assertTrue($battle->effects->has($hero, 'cling_to_life'));
    }

    public function testDeathsHappenAfterEveryActionHasResolved(): void
    {
        $battle = $this->teams(true);
        $this->named($battle, 'Hero')->currentHealth = 10;
        $this->queue($battle, 'Foe', 'attack', 'Hero');
        $this->queue($battle, 'Other', 'defend');
        $this->queue($battle, 'Hero', 'focus');
        $this->queue($battle, 'Ally', 'defend');

        $this->resolveTurn($battle);

        $types = array_map(fn (BattleEvent $event) => $event->type, $this->events($battle));
        $this->assertEquals(4, count(array_keys($types, EventType::Selected)));
        $this->assertGreaterThan(max(array_keys($types, EventType::Selected)), array_search(EventType::Died, $types));
    }

    public function testKillLineWaitsForTheTurnsHealing(): void
    {
        $battle = BattleBuilder::make()->hero('Foe', team: 2, abilities: ['soul_rend'])->hero('Hero')->hero('Ally')->build();
        $hero = $this->named($battle, 'Hero');
        $hero->currentHealth = 10;
        $this->queue($battle, 'Foe', 'soul_rend', 'Hero');
        $this->queue($battle, 'Hero', 'focus');
        $this->queue($battle, 'Ally', 'recover', 'Hero');

        $this->resolveTurn($battle);

        $this->assertFalse($hero->isAlive());
        $this->assertStringContainsString('devours their soul', $this->text($battle));
    }

    public function testKillLineIsSkippedWhenTheTargetSurvives(): void
    {
        $battle = BattleBuilder::make()->hero('Foe', team: 2, abilities: ['soul_rend'])->hero('Hero')->build();
        $this->queue($battle, 'Foe', 'soul_rend', 'Hero');
        $this->queue($battle, 'Hero', 'focus');
        $this->named($battle, 'Hero')->baseStats['health'] = 500;
        $this->named($battle, 'Hero')->currentHealth = 500;

        $this->resolveTurn($battle);

        $this->assertStringNotContainsString('devours their soul', $this->text($battle));
    }

    #[DataProvider('teamOrders')]
    public function testLifestealHealsOnceTheTurnsDamageHasLanded(bool $firstTeamFirst): void
    {
        $battle = $this->teams($firstTeamFirst);
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'lifesteal');
        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->queue($battle, 'Ally', 'defend');
        $this->queue($battle, 'Foe', 'attack', 'Hero');
        $this->queue($battle, 'Other', 'defend');

        $this->resolveTurn($battle);

        $this->assertEquals(100 - 20 + 10, $hero->currentHealth, 'Healing at full health still offsets the hit taken');
    }

    #[DataProvider('teamOrders')]
    public function testBloodrendHealsOnceTheTurnsDamageHasLanded(bool $firstTeamFirst): void
    {
        $battle = $this->ordered($firstTeamFirst, [
            'Hero' => ['team' => 1],
            'Boss' => ['team' => 2, 'stats' => ['health' => 200], 'abilities' => array_merge(HeroClassLoadouts::BASIC_ABILITIES, ['bloodrend'])],
        ]);
        $this->queue($battle, 'Hero', 'attack', 'Boss');
        $this->queue($battle, 'Boss', 'bloodrend', 'Hero');

        $this->resolveTurn($battle);

        $this->assertEquals(200, $this->named($battle, 'Boss')->currentHealth);
        $this->assertEquals(100 - Bloodrend::ATTACK_DAMAGE, $this->named($battle, 'Hero')->currentHealth);
        $this->assertStringContainsString('She feasts, healing 20 health', $this->text($battle), 'She reports what she really restored after the hit');
    }

    #[DataProvider('teamOrders')]
    public function testShadowDanceRefocusesAnAttackerWhoEvades(bool $firstTeamFirst): void
    {
        $battle = $this->ordered($firstTeamFirst, ['Hero' => ['team' => 1], 'Foe' => ['team' => 2]]);
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'shadow_dance');
        $battle->effects->apply($hero, 'focused');
        $battle->evasionRoll = fn ($target) => $target->id === $hero->id;
        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->queue($battle, 'Foe', 'attack', 'Hero');

        $this->resolveTurn($battle);

        $this->assertEquals(100 - 30, $this->named($battle, 'Foe')->currentHealth, 'The attack spent the Focus');
        $this->assertTrue($battle->effects->has($hero, 'focused'), 'The evade earned it back');
    }

    #[DataProvider('teamOrders')]
    public function testShadowDanceNeverAddsASecondFocus(bool $firstTeamFirst): void
    {
        $battle = $this->ordered($firstTeamFirst, ['Hero' => ['team' => 1], 'Foe' => ['team' => 2]]);
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'shadow_dance');
        $battle->evasionRoll = fn ($target) => $target->id === $hero->id;
        $this->queue($battle, 'Hero', 'focus');
        $this->queue($battle, 'Foe', 'attack', 'Hero');

        $this->resolveTurn($battle);

        $this->assertEquals(1, $battle->effects->stacks($hero, 'focused'));
    }

    public function testAFallingHeroGainsNothingFromTheirAction(): void
    {
        $battle = $this->teams(true);
        $hero = $this->named($battle, 'Hero');
        $hero->currentHealth = 5;
        $this->queue($battle, 'Hero', 'focus');
        $this->queue($battle, 'Ally', 'defend');
        $this->queue($battle, 'Foe', 'attack', 'Hero');
        $this->queue($battle, 'Other', 'defend');

        $this->resolveTurn($battle);

        $this->assertFalse($hero->isAlive());
        $this->assertFalse($battle->effects->has($hero, 'focused'));
    }

    #[DataProvider('teamOrders')]
    public function testSameTurnRestorationsKeepTheLargerTick(bool $firstTeamFirst): void
    {
        $battle = $this->ordered($firstTeamFirst, [
            'Hero' => ['team' => 1],
            'Ally' => ['team' => 1, 'stats' => ['recover' => 40]],
            'Foe' => ['team' => 2],
        ]);
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'restoration');
        $battle->effects->apply($this->named($battle, 'Ally'), 'restoration');
        $hero->currentHealth = 20;
        $this->queue($battle, 'Hero', 'recover');
        $this->queue($battle, 'Ally', 'recover', 'Hero');
        $this->queue($battle, 'Foe', 'defend');

        $this->resolveTurn($battle);

        $this->assertEquals(4, $battle->effects->find($hero, 'restoring')->data['amount'], "10% of the Ally's 40");
    }

    /**
     * Builds the combatants in the order given, by team, or with every id reversed.
     *
     * @param array<string, array{team: int, stats?: array<string, int>, abilities?: string[]}> $combatants
     */
    private function ordered(bool $firstTeamFirst, array $combatants): Battle
    {
        $names = array_keys($combatants);
        usort($names, fn ($a, $b) => $combatants[$a]['team'] <=> $combatants[$b]['team']);
        if (!$firstTeamFirst) {
            $names = array_reverse($names);
        }

        $builder = BattleBuilder::make();
        foreach ($names as $name) {
            $builder->hero(
                $name,
                team: $combatants[$name]['team'],
                stats: $combatants[$name]['stats'] ?? [],
                abilities: $combatants[$name]['abilities'] ?? HeroClassLoadouts::BASIC_ABILITIES,
            );
        }

        return $builder->build();
    }

    /**
     * @return BattleEvent[]
     */
    private function events(Battle $battle): array
    {
        return array_merge(...array_map(fn ($entry) => $entry->events, $battle->log->entries()));
    }

    private function text(Battle $battle): string
    {
        return json_encode(array_map(fn (BattleEvent $event) => $event->toArray(), $this->events($battle)));
    }

    private function lastEvent(Battle $battle, EventType $type): BattleEvent
    {
        $events = array_values(array_filter($this->events($battle), fn (BattleEvent $event) => $event->type === $type));

        return $events[array_key_last($events)];
    }
}
