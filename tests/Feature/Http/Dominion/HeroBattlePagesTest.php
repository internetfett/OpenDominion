<?php

namespace OpenDominion\Tests\Feature\Http\Dominion;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Hero;
use OpenDominion\Models\Race;
use OpenDominion\Services\Dominion\HeroBattleService;
use OpenDominion\Tests\AbstractTestCase;

class HeroBattlePagesTest extends AbstractTestCase
{
    use DatabaseTransactions;

    protected function heroDominion(): Dominion
    {
        $user = $this->createAndImpersonateUser();
        $round = $this->createRound('-7 days');
        $dominion = $this->createDominionWithLegacyStats($user, $round, Race::where('name', 'Human')->firstOrFail());
        Hero::create([
            'dominion_id' => $dominion->id,
            'name' => 'Page Hero',
            'class' => 'blacksmith',
            'experience' => 2000,
            'class_data' => [],
        ]);
        $this->selectDominion($dominion->fresh());

        return $dominion->fresh();
    }

    public function testPracticePageListsRegisteredEncounters(): void
    {
        $this->heroDominion();

        $this->get(route('dominion.heroes.battles.practice'))
            ->assertOk()
            ->assertSee('Admiral Varos')
            ->assertSee('Eliza, the Heart of Ice')
            ->assertSee('The Lich King');
    }

    public function testActiveBattlePageShowsSidesEffectsAndActions(): void
    {
        $dominion = $this->heroDominion();
        $battle = app(HeroBattleService::class)->createEncounterBattle('lich_king', [$dominion], 'practice');
        $player = $battle->combatants()->where('dominion_id', $dominion->id)->firstOrFail();
        $player->update(['automated' => false]);

        $this->get(route('dominion.heroes.battles'))
            ->assertOk()
            ->assertSee('Your side')
            ->assertSee('Opponents')
            ->assertSee('Tome of Power')
            ->assertSee('Enrage')
            ->assertSee('Power Source')
            ->assertSee('Attack')
            ->assertSee('Recover')
            ->assertSeeInOrder(['Your side', 'Perform an action', 'Opponents'])
            ->assertDontSee('Actions in queue');
    }

    public function testBattlePageShowsTheHerosCombosFromTheFirstTurn(): void
    {
        $dominion = $this->heroDominion();
        $dominion->hero->update(['experience' => 3500]);
        \OpenDominion\Models\HeroHeroUpgrade::create([
            'hero_id' => $dominion->hero->id,
            'hero_upgrade_id' => \OpenDominion\Models\HeroUpgrade::where('key', 'shield_bash')->firstOrFail()->id,
        ]);
        app(HeroBattleService::class)->createEncounterBattle('heart_of_ice', [$dominion->fresh()], 'practice');

        $this->get(route('dominion.heroes.battles'))
            ->assertOk()
            ->assertSee('title="After using Defend: +5 attack for the next turn."', false)
            ->assertSee('Shield Bash');
    }

    public function testComboBadgeTurnsIntoItsStatusOnceTriggered(): void
    {
        $dominion = $this->heroDominion();
        $dominion->hero->update(['experience' => 3500]);
        \OpenDominion\Models\HeroHeroUpgrade::create([
            'hero_id' => $dominion->hero->id,
            'hero_upgrade_id' => \OpenDominion\Models\HeroUpgrade::where('key', 'shield_bash')->firstOrFail()->id,
        ]);
        $battle = app(HeroBattleService::class)->createEncounterBattle('heart_of_ice', [$dominion->fresh()], 'practice');
        $player = $battle->combatants()->where('dominion_id', $dominion->id)->firstOrFail();

        $this->get(route('dominion.heroes.battles.action', [
            'combatant' => $player->id,
            'action' => 'defend',
        ]))->assertRedirect(route('dominion.heroes.battles'));

        $content = $this->get(route('dominion.heroes.battles'))->assertOk()->getContent();
        $tooltip = 'title="After using Defend: +5 attack for the next turn."';
        $this->assertEquals(1, substr_count($content, $tooltip));
        $this->assertMatchesRegularExpression('/<span class="badge bg-success mb-1" data-bs-toggle="tooltip" ' . preg_quote($tooltip, '/') . '>\s*Shield Bash/', $content);
    }

    public function testCoopQueueIsShownBesideASingleOpponent(): void
    {
        $dominion = $this->heroDominion();
        $ally = $this->createDominionWithLegacyStats($this->createUser(), $dominion->round, Race::where('name', 'Human')->firstOrFail());
        Hero::create([
            'dominion_id' => $ally->id,
            'name' => 'Ally Hero',
            'class' => 'blacksmith',
            'experience' => 2000,
            'class_data' => [],
        ]);
        $battle = app(HeroBattleService::class)->createEncounterBattle('grand_magister', [$dominion, $ally->refresh()], 'practice');
        $battle->combatants()->whereNotNull('hero_id')->update(['automated' => false]);

        $this->get(route('dominion.heroes.battles'))
            ->assertOk()
            ->assertSeeInOrder(['Your side', 'Perform/Queue an action', 'Opponents', 'Grand Magister', 'Actions in queue']);
    }

    public function testQueueingAnActionThroughTheRouteResolvesTheTurn(): void
    {
        $dominion = $this->heroDominion();
        $battle = app(HeroBattleService::class)->createEncounterBattle('heart_of_ice', [$dominion], 'practice');
        $player = $battle->combatants()->where('dominion_id', $dominion->id)->firstOrFail();

        $this->get(route('dominion.heroes.battles.action', [
            'combatant' => $player->id,
            'action' => 'defend',
        ]))->assertRedirect(route('dominion.heroes.battles'));

        $this->assertEquals(2, $battle->fresh()->current_turn);

        $this->get(route('dominion.heroes.battles'))
            ->assertOk()
            ->assertSee('takes a defensive stance');
    }

    public function testBattleReportRendersFinishedTeamBattle(): void
    {
        $dominion = $this->heroDominion();
        $battle = app(HeroBattleService::class)->createEncounterBattle('admiral_varos', [$dominion], 'practice');
        $battle->combatants()->update(['automated' => true]);
        app(HeroBattleService::class)->processTurn($battle->fresh());

        $this->get(route('dominion.heroes.battles.report', ['battle' => $battle->id]))
            ->assertOk()
            ->assertSee('Admiral Varos')
            ->assertSee('Combat Log');
    }

    /**
     * Splits the heroes page header into its Dominion and Hero Battle sections.
     *
     * @return array{dominion: string, heroBattle: string}
     */
    protected function heroSections(string $content): array
    {
        $dominionStart = strpos($content, 'ra-tower"></i> Dominion Bonuses');
        $heroBattleStart = strpos($content, 'ra-axe"></i> Hero Battles');
        $upgradesStart = strpos($content, 'id="hero-upgrades"');
        $this->assertNotFalse($dominionStart);
        $this->assertNotFalse($heroBattleStart);
        $this->assertNotFalse($upgradesStart);

        return [
            'dominion' => substr($content, $dominionStart, $heroBattleStart - $dominionStart),
            'heroBattle' => substr($content, $heroBattleStart, $upgradesStart - $heroBattleStart),
        ];
    }

    public function testHeroesPageShowsTheHeroBannerWithLevelProgress(): void
    {
        $this->heroDominion();

        $this->get(route('dominion.heroes'))
            ->assertOk()
            ->assertSee('Page Hero')
            ->assertSee('Level 4 Blacksmith')
            ->assertSee('2,000 / 2,300 XP · 300 to level 5');
    }

    public function testHeroesPageSplitsUpgradeSlotsByWhatTheyAffect(): void
    {
        $dominion = $this->heroDominion();
        $dominion->hero->update(['experience' => 1200]);
        \OpenDominion\Models\HeroHeroUpgrade::create([
            'hero_id' => $dominion->hero->id,
            'hero_upgrade_id' => \OpenDominion\Models\HeroUpgrade::where('key', 'backstab')->firstOrFail()->id,
        ]);

        $sections = $this->heroSections($this->get(route('dominion.heroes'))->assertOk()->getContent());

        $this->assertStringContainsString('Lv 1 · Doctrine', $sections['dominion']);
        $this->assertStringContainsString('Lv 2 · Item or Magic', $sections['dominion']);
        $this->assertStringContainsString('Choose upgrade', $sections['dominion']);
        $this->assertStringContainsString('<a href="#hero-upgrades" class="hero-tile hero-tile-open" data-upgrade-level="2">', $sections['dominion']);
        $this->assertStringContainsString('Reach level 4', $sections['dominion']);
        $this->assertStringNotContainsString('Discipline', $sections['dominion']);

        $this->assertStringContainsString('Lv 3 · Discipline', $sections['heroBattle']);
        $this->assertStringContainsString('Backstab', $sections['heroBattle']);
        $this->assertStringContainsString('against a target that was Focused at the start of the turn', $sections['heroBattle']);
        $this->assertStringContainsString('Lv 5 · Gear', $sections['heroBattle']);
        $this->assertStringContainsString('Lv 7 · Combo', $sections['heroBattle']);
        $this->assertStringContainsString('Reach level 7', $sections['heroBattle']);
        $this->assertStringNotContainsString('Choose upgrade', $sections['heroBattle']);
        $this->assertStringNotContainsString('Lv 9', $sections['heroBattle']);
    }

    public function testHeroesPageShowsRatingAndGearStatChanges(): void
    {
        $dominion = $this->heroDominion();
        $dominion->hero->forceFill(['experience' => 2300, 'combat_rating' => 1043, 'stat_combat_wins' => 7, 'stat_combat_losses' => 4, 'stat_combat_draws' => 1])->save();
        \OpenDominion\Models\HeroHeroUpgrade::create([
            'hero_id' => $dominion->hero->id,
            'hero_upgrade_id' => \OpenDominion\Models\HeroUpgrade::where('key', 'greatsword')->firstOrFail()->id,
        ]);

        $heroBattle = $this->heroSections($this->get(route('dominion.heroes'))->assertOk()->getContent())['heroBattle'];

        $this->assertMatchesRegularExpression('/Rating<\/div>\s*<div class="hero-stat-value">1043<\/div>\s*<div class="hero-stat-change">7-4-1<\/div>/', $heroBattle);
        $this->assertMatchesRegularExpression('/Attack<\/div>\s*<div class="hero-stat-value">42<\/div>\s*<div class="hero-stat-change text-green">\+2<\/div>/', $heroBattle);
        $this->assertMatchesRegularExpression('/Evasion<\/div>\s*<div class="hero-stat-value">0<\/div>\s*<div class="hero-stat-change text-red">-10<\/div>/', $heroBattle);
        $this->assertMatchesRegularExpression('/Defense<\/div>\s*<div class="hero-stat-value">20<\/div>\s*<div class="hero-stat-change"><\/div>/', $heroBattle);
    }

    public function testHeroesPageListsPlayedClassesBeforeUnplayedOnes(): void
    {
        $dominion = $this->heroDominion();
        $dominion->hero->update(['class_data' => [
            'architect' => [
                'key' => 'architect',
                'level' => 3,
                'experience' => 1200,
                'perk_type' => 'construction_cost',
                'bonus' => -1.875,
            ],
        ]]);

        $dominionSection = $this->heroSections($this->get(route('dominion.heroes'))->assertOk()->getContent())['dominion'];
        $unplayedStart = strpos($dominionSection, 'id="hero-unplayed-classes"');
        $this->assertNotFalse($unplayedStart);
        $played = substr($dominionSection, 0, $unplayedStart);
        $unplayed = substr($dominionSection, $unplayedStart);

        $this->assertMatchesRegularExpression('/Blacksmith<\/div>.*?Lv 4<\/span>\s*<span class="hero-class-active">Active<\/span>/s', $played);
        $this->assertMatchesRegularExpression('/Architect<\/div>.*?\(halved\).*?Lv 3<\/span>/s', $played);
        $this->assertStringContainsString(route('dominion.heroes.change-class', 'architect'), $played);
        $this->assertStringContainsString('Farmer', $unplayed);
        $this->assertStringNotContainsString('Farmer', $played);

        $unplayedCount = count(app(\OpenDominion\Helpers\HeroHelper::class)->getClasses()) - 2;
        $this->assertStringContainsString("Show {$unplayedCount} more classes", $unplayed);
    }

    public function testHeroesPageHidesTheShrineBonusWithoutShrines(): void
    {
        $this->heroDominion();

        $this->get(route('dominion.heroes'))
            ->assertOk()
            ->assertDontSee('Your hero bonuses are being increased by')
            ->assertDontSee('Before your hero bonus increase');
    }

    public function testHeroesPageShowsTheShrineBonusWithShrines(): void
    {
        $dominion = $this->heroDominion();
        $dominion->update(['building_shrine' => 5]);

        $dominionSection = $this->heroSections($this->get(route('dominion.heroes'))->assertOk()->getContent())['dominion'];
        $this->assertMatchesRegularExpression('/Your hero bonuses are being increased by \d+\.\d{2}%/', $dominionSection);
        $this->assertMatchesRegularExpression('/-\d+\.\d{2}% military training cost<\/span>\s*<span class="hero-class-note"[^>]*>\(-0\.80%\)<\/span>/', $dominionSection);
    }

    public function testUpgradeTablesAreSplitByWhatTheyAffect(): void
    {
        $this->heroDominion();

        $content = $this->get(route('dominion.heroes'))
            ->assertOk()
            ->assertDontSee('<th>Effect</th>', false)
            ->getContent();

        $upgradesStart = strpos($content, 'id="hero-upgrades"');
        $upgrades = substr($content, $upgradesStart, strpos($content, '</form>', $upgradesStart) - $upgradesStart);
        $heroBattleStart = strpos($upgrades, 'ra-axe me-1"></i> Combat Upgrades');
        $this->assertNotFalse($heroBattleStart);
        $dominion = substr($upgrades, 0, $heroBattleStart);
        $heroBattle = substr($upgrades, $heroBattleStart);

        $this->assertStringContainsString('ra-tower me-1"></i> Dominion Upgrades', $dominion);
        $this->assertStringContainsString('Experience gains from invasion and exploration are doubled', $dominion);
        $this->assertStringContainsString('-1 wizard strength cost of self spells', $dominion);
        $this->assertStringNotContainsString('Backstab', $dominion);
        $this->assertStringNotContainsString('Greatsword', $dominion);

        $this->assertMatchesRegularExpression('/Your attacks deal \+5 damage against a target that was Focused/', $heroBattle);
        $this->assertMatchesRegularExpression('/\+2 Attack, -10 Evasion/', $heroBattle);
        $this->assertMatchesRegularExpression('/After using Defend: \+5 attack for the next turn\./', $heroBattle);
        $this->assertStringNotContainsString('wizard strength', $heroBattle);
    }
}
