<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use OpenDominion\HeroCombat\Content\Effects\Techniques\BattleFury;
use OpenDominion\HeroCombat\Content\Effects\Techniques\Rejuvenation;
use OpenDominion\HeroCombat\Content\Effects\Techniques\Riposte;
use OpenDominion\HeroCombat\Engine\ActionValidator;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\Tests\Unit\HeroCombat\Support\BattleBuilder;
use OpenDominion\Tests\Unit\HeroCombat\Support\BuildsBattles;
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

    public function testAttunementAllowsTwoFocusStacks(): void
    {
        $battle = $this->duel('attunement', 'idle');
        $hero = $this->named($battle, 'Hero');
        $validator = new ActionValidator($battle);

        $battle->effects->apply($hero, 'focused');
        $this->assertTrue($validator->canPerform($hero, 'focus'));

        $battle->effects->apply($hero, 'focused');
        $this->assertEquals(2, $battle->effects->stacks($hero, 'focused'));
        $this->assertFalse($validator->canPerform($hero, 'focus'));
    }

    public function testRejuvenationHealsMostNowAndTheRestOverThreeTurns(): void
    {
        $battle = $this->duel('rejuvenation', 'idle');
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
        $this->assertFalse($battle->effects->has($hero, 'rejuvenating'));
    }

    public function testRecastingRejuvenationForfeitsTheRemainingTick(): void
    {
        $battle = $this->duel('rejuvenation', 'idle');
        $hero = $this->named($battle, 'Hero');
        $hero->currentHealth = 20;

        foreach (['recover', 'defend', 'recover'] as $ability) {
            $this->queue($battle, 'Hero', $ability);
            $this->resolveTurn($battle);
        }

        $this->assertEquals(20 + 16 + 2 + 2 + 16, $hero->currentHealth, 'Two ticks of the first heal, then the recast');
        $this->assertEquals(3, $battle->effects->find($hero, 'rejuvenating')->remainingTurns, 'The recast starts a fresh set of ticks');
    }

    public function testRiposteRemovesTheCounterCooldownButWeakensIt(): void
    {
        $battle = $this->duel('riposte');
        $hero = $this->named($battle, 'Hero');
        $validator = new ActionValidator($battle);

        $this->queue($battle, 'Hero', 'counter');
        $damage = $this->damageTo($battle, 'Foe', fn () => $this->resolveTurn($battle));

        $this->assertEquals((int) round((40 + 10 - 20) * Riposte::COUNTER_MULTIPLIER), $damage);
        $this->assertTrue($validator->canPerform($hero, 'counter'));
    }

    public function testShadowDanceDodgesInsteadOfBlocking(): void
    {
        $battle = $this->duel('shadow_dance', foeStats: ['attack' => 60]);
        $hero = $this->named($battle, 'Hero');
        $battle->random->ints = [0];

        $this->queue($battle, 'Hero', 'defend');
        $taken = $this->damageTo($battle, 'Hero', fn () => $this->resolveTurn($battle));

        $this->assertEquals(0, $taken, 'Evaded an unfocused attack completely');
        $this->assertTrue($battle->effects->has($hero, 'focused'), 'Dodging finds an opening');
    }

    public function testShadowDanceAddsEvasionOnTopOfTheBlock(): void
    {
        $battle = $this->duel('shadow_dance', foeStats: ['attack' => 60]);
        $hero = $this->named($battle, 'Hero');

        $this->declare($battle, 'Hero', 'defend');

        $this->assertEquals(40, $battle->stat($hero, Stat::Defense));
        $this->assertEquals(30, $battle->stat($hero, Stat::Evasion));
    }

    // ------------------------------------------------------------------
    // Forms
    // ------------------------------------------------------------------

    public function testFormStatusAppliesForExactlyTheNextTurn(): void
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

    public function testFormStatusDoesNotHelpDuringTheOpenerTurn(): void
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

    public function testFormsOnlyTriggerOnTheirOpener(): void
    {
        $battle = $this->duel('spellweave', 'idle');
        $hero = $this->named($battle, 'Hero');

        $this->queue($battle, 'Hero', 'attack', 'Foe');
        $this->resolveTurn($battle);
        $this->assertFalse($battle->effects->has($hero, 'spellweave_status'));

        $this->queue($battle, 'Hero', 'focus');
        $this->resolveTurn($battle);
        $this->assertEquals(15, $battle->stat($hero, Stat::Counter));
    }

    public function testFormTriggersAreHiddenButStatusesAreVisible(): void
    {
        $registry = \OpenDominion\Providers\HeroCombatServiceProvider::buildRegistry();

        foreach (['shield_bash', 'second_wind', 'spellweave', 'arcane_ward', 'ambush', 'feint'] as $form) {
            $this->assertFalse($registry->effect($form)->visible());
            $this->assertTrue($registry->effect("{$form}_status")->visible());
        }
    }
}
