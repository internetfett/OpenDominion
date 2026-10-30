<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use OpenDominion\HeroCombat\Content\Effects\Techniques\Backstab;
use OpenDominion\HeroCombat\Content\Effects\Techniques\BattleFury;
use OpenDominion\HeroCombat\Content\Effects\Techniques\ManaShield;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\Tests\Unit\HeroCombat\Support\BattleBuilder;
use OpenDominion\Tests\Unit\HeroCombat\Support\BuildsBattles;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TechniquesTest extends TestCase
{
    use BuildsBattles;

    /**
     * Hero vs Foe. An 'idle' foe has no abilities and passes every turn.
     */
    private function duel(string $technique, string $foeAi = 'attack', array $heroStats = [], array $foeStats = []): Battle
    {
        $battle = BattleBuilder::make()
            ->hero('Hero', stats: $heroStats)
            ->npc(
                'Foe',
                stats: array_merge(['health' => 500], $foeStats),
                abilities: $foeAi === 'idle' ? [] : ['attack', 'defend', 'focus', 'counter', 'recover'],
                ai: $foeAi === 'idle' ? 'attack' : $foeAi,
            )
            ->build();
        $battle->effects->apply($this->named($battle, 'Hero'), $technique);

        return $battle;
    }

    private function damageTo(Battle $battle, string $name, callable $act): int
    {
        $before = $this->named($battle, $name)->currentHealth;
        $act();

        return $before - $this->named($battle, $name)->currentHealth;
    }

    // ------------------------------------------------------------------
    // Disciplines
    // ------------------------------------------------------------------

    public function testOverpowerIgnoresFiveDefenseOnlyAgainstDefenders(): void
    {
        $battle = $this->duel('overpower', 'idle', heroStats: ['attack' => 60]);
        $this->declare($battle, 'Foe', 'defend');

        $this->assertEquals(60 - 40 + 5, $this->damageTo($battle, 'Foe', fn () => $this->perform($battle, 'Hero', 'attack', 'Foe')));

        $battle->effects->removeByKey($this->named($battle, 'Foe'), 'defending');
        $this->assertEquals(40, $this->damageTo($battle, 'Foe', fn () => $this->perform($battle, 'Hero', 'attack', 'Foe')));
    }

    public function testBattleFuryRampsWithConsecutiveAttacksAndResets(): void
    {
        $battle = $this->duel('battle_fury', 'idle');
        $hero = $this->named($battle, 'Hero');

        $damages = [];
        for ($i = 0; $i < 4; $i++) {
            $this->queue($battle, 'Hero', 'attack', 'Foe');
            $damages[] = $this->damageTo($battle, 'Foe', fn () => $this->resolveTurn($battle));
        }

        $step = BattleFury::ATTACK_PER_STACK;
        $this->assertEquals([20, 20 + $step, 20 + 2 * $step, 20 + 3 * $step], $damages, 'Capped at three stacks');
        $this->assertEquals(20 - 3 * BattleFury::DEFENSE_PER_STACK, $battle->stat($hero, Stat::Defense), 'Defense drops half as fast as attack rises');

        $this->queue($battle, 'Hero', 'defend');
        $this->resolveTurn($battle);
        $this->assertEquals(40, $battle->stat($hero, Stat::Attack));
        $this->assertEquals(20, $battle->stat($hero, Stat::Defense));
    }

    public function testRestorationHealsMostNowAndTheRestOverThreeTurns(): void
    {
        $battle = $this->duel('restoration', 'idle');
        $hero = $this->named($battle, 'Hero');
        $hero->currentHealth = 40;

        $this->declare($battle, 'Hero', 'recover');
        $this->assertTrue($battle->effects->has($hero, 'recovering'), 'The defense penalty still applies');
        $battle->state->intents = [];

        $this->queue($battle, 'Hero', 'recover');
        $this->resolveTurn($battle);
        $this->assertEquals(40 + 16, $hero->currentHealth, '80% of 20 immediately');

        $health = [];
        for ($i = 0; $i < 4; $i++) {
            $this->queue($battle, 'Hero', 'defend');
            $this->resolveTurn($battle);
            $health[] = $hero->currentHealth;
        }

        $this->assertEquals([58, 60, 62, 62], $health, '10% at the start of each of the next three turns');
        $this->assertFalse($battle->effects->has($hero, 'restoring'));
    }

    public function testRecastingRestorationForfeitsTheRemainingTick(): void
    {
        $battle = $this->duel('restoration', 'idle');
        $hero = $this->named($battle, 'Hero');
        $hero->currentHealth = 20;

        foreach (['recover', 'defend', 'recover'] as $ability) {
            $this->queue($battle, 'Hero', $ability);
            $this->resolveTurn($battle);
        }

        $this->assertEquals(20 + 16 + 2 + 2 + 16, $hero->currentHealth, 'Two ticks of the first heal, then the recast');
        $this->assertEquals(3, $battle->effects->find($hero, 'restoring')->remainingTurns, 'The recast starts a fresh set of ticks');
    }

    public function testManaShieldAddsDefenseOnlyWhileFocused(): void
    {
        $battle = $this->duel('mana_shield', 'idle');
        $hero = $this->named($battle, 'Hero');

        $this->assertEquals(20, $battle->stat($hero, Stat::Defense));

        $this->queue($battle, 'Hero', 'focus');
        $this->resolveTurn($battle);
        $this->assertEquals(20 + ManaShield::DEFENSE, $battle->stat($hero, Stat::Defense));

        $this->queue($battle, 'Hero', 'counter');
        $this->resolveTurn($battle);
        $this->assertEquals(20 + ManaShield::DEFENSE, $battle->stat($hero, Stat::Defense), 'Countering keeps the Focus and the bonus');

        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->resolveTurn($battle);
        $this->assertEquals(20, $battle->stat($hero, Stat::Defense), 'Attacking spends the Focus');
    }

    public function testShadowDanceGrantsFocusOnEvade(): void
    {
        $battle = $this->duel('shadow_dance', foeStats: ['attack' => 60]);
        $hero = $this->named($battle, 'Hero');
        $battle->evasionRoll = fn ($target) => $target->id === $hero->id;

        $this->queue($battle, 'Hero', 'defend');
        $taken = $this->damageTo($battle, 'Hero', fn () => $this->resolveTurn($battle));

        $this->assertEquals(10, $taken, 'An evaded hit still deals half');
        $this->assertTrue($battle->effects->has($hero, 'focused'));
        $this->assertEquals(1, $battle->effects->stacks($hero, 'focused'));
    }

    public function testShadowDanceNeedsAnEvade(): void
    {
        $battle = $this->duel('shadow_dance', foeStats: ['attack' => 60]);
        $hero = $this->named($battle, 'Hero');
        $battle->evasionRoll = fn () => false;

        $this->queue($battle, 'Hero', 'defend');
        $this->resolveTurn($battle);

        $this->assertFalse($battle->effects->has($hero, 'focused'));
        $this->assertEquals(0, $battle->stat($hero, Stat::Evasion), 'Defend no longer adds evasion');
    }

    public function testBackstabHitsFocusedTargetsHarder(): void
    {
        $battle = $this->duel('backstab', 'idle');

        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->assertEquals(20, $this->damageTo($battle, 'Foe', fn () => $this->resolveTurn($battle)));

        $battle->effects->apply($this->named($battle, 'Foe'), 'focused');
        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->assertEquals(20 + Backstab::BONUS_DAMAGE, $this->damageTo($battle, 'Foe', fn () => $this->resolveTurn($battle)));
    }

    public function testBackstabCountsFocusFromTheStartOfTheTurn(): void
    {
        $battle = BattleBuilder::make()
            ->npc('Foe', stats: ['health' => 500], abilities: ['attack'], ai: 'attack')
            ->hero('Hero')
            ->build();
        $hero = $this->named($battle, 'Hero');
        $foe = $this->named($battle, 'Foe');
        $battle->effects->apply($hero, 'backstab');
        $battle->effects->apply($foe, 'focused');
        $this->assertLessThan($hero->id, $foe->id, 'The Foe acts first and spends its Focus before the Hero strikes');

        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $damage = $this->damageTo($battle, 'Foe', fn () => $this->resolveTurn($battle));

        $this->assertFalse($battle->effects->has($foe, 'focused'));
        $this->assertEquals(20 + Backstab::BONUS_DAMAGE, $damage);
    }

    // ------------------------------------------------------------------
    // Combos
    // ------------------------------------------------------------------

    public function testComboStatusAppliesForExactlyTheNextTurn(): void
    {
        $battle = $this->duel('shield_bash', 'idle');
        $hero = $this->named($battle, 'Hero');

        $this->queue($battle, 'Hero', 'defend');
        $this->resolveTurn($battle);
        $this->assertTrue($battle->effects->has($hero, 'shield_bash_status'));
        $this->assertEquals('Shield Bash', $battle->registry->effect('shield_bash_status')->name());

        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->assertEquals(25, $this->damageTo($battle, 'Foe', fn () => $this->resolveTurn($battle)), '+5 attack the turn after Defend');
        $this->assertFalse($battle->effects->has($hero, 'shield_bash_status'));

        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->assertEquals(20, $this->damageTo($battle, 'Foe', fn () => $this->resolveTurn($battle)));
    }

    public function testComboStatusDoesNotHelpDuringTheOpenerTurn(): void
    {
        $battle = $this->duel('feint', 'idle');
        $hero = $this->named($battle, 'Hero');

        $this->perform($battle, 'Hero', 'attack', 'Foe');
        $battle->dispatcher->run(\OpenDominion\HeroCombat\Engine\Effects\Hook::AbilityUsed, [$hero], new \OpenDominion\HeroCombat\Engine\ActionContext($battle, $hero, $battle->registry->ability('attack'), new \OpenDominion\HeroCombat\Engine\Intent($hero->id, 'attack')), $battle);

        $this->assertTrue($battle->effects->has($hero, 'feint_status'));
        $this->assertEquals(0, $battle->stat($hero, Stat::Evasion));

        $battle->effects->tickTurnEnd();
        $battle->state->turn++;
        $this->assertEquals(10, $battle->stat($hero, Stat::Evasion));
    }

    public function testCombosOnlyTriggerOnTheirOpener(): void
    {
        $battle = $this->duel('spellweave', 'idle');
        $hero = $this->named($battle, 'Hero');

        $this->queue($battle, 'Hero', 'focus');
        $this->resolveTurn($battle);
        $this->assertFalse($battle->effects->has($hero, 'spellweave_status'));

        $this->queue($battle, 'Hero', 'defend');
        $this->resolveTurn($battle);
        $this->assertTrue($battle->effects->has($hero, 'spellweave_status'));
    }

    /**
     * @return array<string, array{string, string, Stat, int}>
     */
    public static function combos(): array
    {
        return [
            'feint' => ['feint', 'attack', Stat::Evasion, 0 + 10],
            'shield bash' => ['shield_bash', 'defend', Stat::Attack, 40 + 5],
            'spellweave' => ['spellweave', 'defend', Stat::Focus, 10 + 10],
            'riposte' => ['riposte', 'counter', Stat::Attack, 40 + 5],
            'second wind' => ['second_wind', 'recover', Stat::Attack, 40 + 5],
            'wellspring' => ['wellspring', 'focus', Stat::Recover, 20 + 5],
        ];
    }

    #[DataProvider('combos')]
    public function testEachComboBoostsItsStatTheTurnAfterItsOpener(string $combo, string $opener, Stat $stat, int $expected): void
    {
        $battle = $this->duel($combo, 'idle');
        $hero = $this->named($battle, 'Hero');

        $this->queue($battle, 'Hero', $opener, $opener === 'attack' ? 'Foe' : null);
        $this->resolveTurn($battle);

        $this->assertEquals($expected, $battle->stat($hero, $stat));
    }

    public function testCombosAndTheirStatusesAreVisible(): void
    {
        $registry = \OpenDominion\Providers\HeroCombatServiceProvider::buildRegistry();

        foreach (array_column(self::combos(), 0) as $combo) {
            $this->assertTrue($registry->effect($combo)->visible());
            $this->assertTrue($registry->effect("{$combo}_status")->visible());
        }
    }
}
