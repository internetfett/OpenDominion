<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use OpenDominion\HeroCombat\Content\Loadouts\HeroClassLoadouts;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\Tests\Unit\HeroCombat\Support\BattleBuilder;
use OpenDominion\Tests\Unit\HeroCombat\Support\BuildsBattles;
use OpenDominion\Tests\Unit\HeroCombat\Support\ScriptedRandomSource;
use PHPUnit\Framework\TestCase;

class ClassAbilitiesTest extends TestCase
{
    use BuildsBattles;

    public function testStoneShieldShieldsTheCasterUpToTwenty(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->npc('Foe')->build();
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'shield', null, 1, ['pool' => 5]);

        $this->declare($battle, 'Hero', 'stone_shield');

        $this->assertEquals(20, $battle->effects->find($hero, 'shield')->data['pool']);
    }

    public function testForgeStacksAttack(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->npc('Foe')->build();

        $this->perform($battle, 'Hero', 'forge');
        $this->perform($battle, 'Hero', 'forge');

        $this->assertEquals(40 + 2 * 4, $battle->stat($this->named($battle, 'Hero'), Stat::Attack));
    }

    public function testForgeAndCombatAnalysisHaveTwoUsesPerBattle(): void
    {
        $battle = BattleBuilder::make()->hero('Hero', abilities: ['attack', 'defend', 'focus', 'counter', 'recover', 'forge', 'combat_analysis'])->npc('Foe')->build();
        $hero = $this->named($battle, 'Hero');
        $validator = new \OpenDominion\HeroCombat\Engine\ActionValidator($battle);

        foreach (['forge', 'combat_analysis'] as $ability) {
            $this->assertEquals(2, $validator->chargesRemaining($hero, $ability));
            $validator->recordUse($hero, $battle->registry->ability($ability));
            $validator->recordUse($hero, $battle->registry->ability($ability));
            $this->assertEquals(0, $validator->chargesRemaining($hero, $ability));
            $hero->lastAction = null;
            $this->assertFalse($validator->canPerform($hero, $ability), "{$ability} is out of uses");
        }
    }

    public function testCombatAnalysisLowersDefense(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->npc('Foe')->build();

        $this->perform($battle, 'Hero', 'combat_analysis', 'Foe');

        $this->assertEquals(20 - 4, $battle->stat($this->named($battle, 'Foe'), Stat::Defense));
    }

    public function testShadowStrikeCannotBeEvadedOrCounteredButHitsForLess(): void
    {
        $random = new ScriptedRandomSource();
        $random->defaultInt = 0;
        $battle = BattleBuilder::make()->hero('Hero', stats: ['attack' => 60])->npc('Foe', stats: ['evasion' => 100])->build($random);
        $this->declare($battle, 'Foe', 'counter');

        $this->perform($battle, 'Hero', 'shadow_strike', 'Foe');

        $this->assertEquals(100 - (60 - 5 - 20), $this->named($battle, 'Foe')->currentHealth);
        $this->assertEquals(100, $this->named($battle, 'Hero')->currentHealth, 'The Counter never fires');
    }

    public function testVolatileMixtureHitsForOneAndAHalf(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->npc('Foe')->build();

        $this->perform($battle, 'Hero', 'volatile_mixture', 'Foe');

        $this->assertEquals(70, $this->named($battle, 'Foe')->currentHealth);
    }

    public function testVolatileMixtureBackfireStillDrawsTheCounter(): void
    {
        $random = new ScriptedRandomSource();
        $random->chances = [false];
        $battle = BattleBuilder::make()->hero('Hero')->npc('Foe')->build($random);
        $this->declare($battle, 'Foe', 'counter');

        $text = $this->perform($battle, 'Hero', 'volatile_mixture', 'Foe');

        $this->assertEquals(100, $this->named($battle, 'Foe')->currentHealth);
        $this->assertEquals(100 - 20 - 30, $this->named($battle, 'Hero')->currentHealth);
        $this->assertStringContainsString('explodes prematurely', $text);
        $this->assertStringContainsString('distracted alchemist', $text);
    }

    public function testAGuardingAllyDoesNotTakeTheBackfire(): void
    {
        $random = new ScriptedRandomSource();
        $random->chances = [false];
        $battle = BattleBuilder::make()
            ->hero('Hero', abilities: ['attack', 'defend', 'focus', 'counter', 'recover', 'volatile_mixture'])
            ->hero('Ally')
            ->hero('Foe', team: 2)
            ->build($random);
        $this->queue($battle, 'Hero', 'volatile_mixture', 'Foe');
        $this->queue($battle, 'Ally', 'defend', 'Hero');
        $this->queue($battle, 'Foe', 'focus');

        $this->resolveTurn($battle);

        $this->assertEquals(100 - 20, $this->named($battle, 'Hero')->currentHealth);
        $this->assertEquals(100, $this->named($battle, 'Ally')->currentHealth);
    }

    public function testDetonationsHitEveryEnemyThroughDefensesAndShields(): void
    {
        $battle = BattleBuilder::make()
            ->hero('Hero')
            ->npc('Golem A', stats: ['defense' => 100])
            ->npc('Golem B')
            ->build();
        $battle->effects->apply($this->named($battle, 'Golem A'), 'shield', null, 1, ['pool' => 20]);
        $battle->effects->apply($this->named($battle, 'Golem B'), 'cling_to_life');
        $this->named($battle, 'Golem B')->currentHealth = 10;

        $text = $this->perform($battle, 'Hero', 'demolish');

        $this->assertEquals(70, $this->named($battle, 'Golem A')->currentHealth);
        $this->assertEquals(0, $this->named($battle, 'Golem B')->currentHealth);
        $this->assertStringContainsString('30 damage to all enemies', $text);
    }

    public function testBladeFlurryStrikesTwiceAtReducedDamage(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->npc('Foe')->build();

        $this->perform($battle, 'Hero', 'blade_flurry', 'Foe');

        $this->assertEquals(72, $this->named($battle, 'Foe')->currentHealth, '85% of 40 is 34, minus 20 defense, twice');
    }

    public function testCleanseRemovesCurses(): void
    {
        $registry = \OpenDominion\Providers\HeroCombatServiceProvider::buildRegistry();
        $registry->registerEffect(new class extends \OpenDominion\HeroCombat\Content\AbstractEffect {
            public function key(): string
            {
                return 'hex';
            }

            public function name(): string
            {
                return 'Hex';
            }

            public function tags(): array
            {
                return [\OpenDominion\HeroCombat\Engine\CombatTag::Curse];
            }
        });
        $battle = BattleBuilder::make()->withRegistry($registry)->hero('Hero')->build();
        $hero = $this->named($battle, 'Hero');
        $battle->effects->apply($hero, 'hex');
        $battle->effects->apply($hero, 'frostbitten');

        $this->perform($battle, 'Hero', 'cleanse');

        $this->assertFalse($battle->effects->has($hero, 'hex'));
        $this->assertTrue($battle->effects->has($hero, 'frostbitten'), 'Only curses are cleansed');
    }

    public function testMendingBoostsRecoverAndKeepsTheFocusForTheNextAttack(): void
    {
        $battle = BattleBuilder::make()->hero('Hero')->hero('Foe', team: 2)->build();
        $hero = $this->named($battle, 'Hero');
        $foe = $this->named($battle, 'Foe');
        $hero->currentHealth = 50;
        $battle->effects->apply($hero, 'mending');
        $this->queue($battle, 'Hero', 'focus');
        $this->queue($battle, 'Foe', 'focus');
        $this->resolveTurn($battle);

        $this->queue($battle, 'Hero', 'recover');
        $this->queue($battle, 'Foe', 'counter');
        $this->resolveTurn($battle);

        $this->assertEquals(50 + 30, $hero->currentHealth, 'Recover 20 + focus 10');
        $this->assertTrue($battle->effects->has($hero, 'focused'), 'Healing keeps the Focus');

        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->queue($battle, 'Foe', 'defend');
        $this->resolveTurn($battle);

        $this->assertEquals(100 - (50 - 40), $foe->currentHealth, 'The Focus lands on the next attack');
        $this->assertFalse($battle->effects->has($hero, 'focused'));
    }

    public function testEachSignatureBelongsToOneClass(): void
    {
        $loadouts = new HeroClassLoadouts();

        $this->assertEquals('infiltrator', $loadouts->classFor('shadow_strike'));
        $this->assertEquals('farmer', $loadouts->classFor('hardiness'));
        $this->assertNull($loadouts->classFor('blade_flurry'), 'Not a class signature');
        $this->assertCount(count(HeroClassLoadouts::SIGNATURES), array_unique(HeroClassLoadouts::SIGNATURES));
    }

    public function testGrantsAddToTheBasicAbilities(): void
    {
        $loadouts = new HeroClassLoadouts();

        $this->assertEquals([...HeroClassLoadouts::BASIC_ABILITIES, 'fortify'], $loadouts->withGrants(HeroClassLoadouts::BASIC_ABILITIES, ['fortify']), 'Signatures are a sixth action now');
        $this->assertEquals([...HeroClassLoadouts::BASIC_ABILITIES, 'shadow_strike'], $loadouts->withGrants(HeroClassLoadouts::BASIC_ABILITIES, ['shadow_strike', 'shadow_strike']));
    }

    public function testEverySignatureIsRegistered(): void
    {
        $registry = \OpenDominion\Providers\HeroCombatServiceProvider::buildRegistry();

        foreach (HeroClassLoadouts::SIGNATURES as $class => $key) {
            $this->assertTrue($registry->hasAbility($key) || $registry->hasEffect($key), "{$class} signature {$key}");
        }
    }
}
