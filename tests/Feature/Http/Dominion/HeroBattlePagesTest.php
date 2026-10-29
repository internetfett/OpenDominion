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

    public function testHeroesPageShowsUpgradeSlotsAndTechniqueDescriptions(): void
    {
        $dominion = $this->heroDominion();
        $dominion->hero->update(['experience' => 1200]);
        \OpenDominion\Models\HeroHeroUpgrade::create([
            'hero_id' => $dominion->hero->id,
            'hero_upgrade_id' => \OpenDominion\Models\HeroUpgrade::where('key', 'riposte')->firstOrFail()->id,
        ]);

        $this->get(route('dominion.heroes'))
            ->assertOk()
            ->assertSee('Level 3: Riposte', false)
            ->assertSee('Level 9: Locked', false)
            ->assertSee('Counter can be used every turn')
            ->assertSee('Shield Bash')
            ->assertSee('Greatsword')
            ->assertDontSee('Discipline</td>', false);
    }
}
