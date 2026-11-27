<?php

namespace OpenDominion\Tests\Unit\Services\Dominion;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Relic;
use OpenDominion\Models\RelicTracking;
use OpenDominion\Models\Round;
use OpenDominion\Services\Dominion\RelicService;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class RelicServiceTest extends AbstractBrowserKitTestCase
{
    use DatabaseTransactions;

    protected Round $round;

    protected Dominion $dominion;

    protected Dominion $target;

    protected RelicService $relicService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->round = $this->createRound('-5 days midnight');
        $this->dominion = $this->createDominionWithLegacyStats($this->createUser(), $this->round);
        $this->target = $this->createDominionWithLegacyStats($this->createUser(), $this->round);
        $this->relicService = app(RelicService::class);

        $this->mockRandomChance(false);
    }

    protected function tearDown(): void
    {
        $this->mockRandomChance(null);

        parent::tearDown();
    }

    protected function mockRandomChance(?bool $value): void
    {
        global $mockRandomChance;
        $mockRandomChance = $value;
    }

    protected function enableSearch(Dominion $dominion, int $intensity = 2, bool $stopOnFind = false): void
    {
        $dominion->settings = ['relics' => ['search_intensity' => $intensity, 'search_stop_on_find' => $stopOnFind]];
    }

    public function testGrantCreatesAHeldUnboundRelic(): void
    {
        $relic = $this->relicService->grant($this->dominion, Relic::SOURCE_SEARCH, 'epic');

        $this->assertNotNull($relic);
        $this->assertEquals($this->dominion->id, $relic->dominion_id);
        $this->assertEquals('epic', $relic->rarity);
        $this->assertFalse($relic->is_bound);
        $this->assertTrue($relic->isHeld());
        $this->assertContains($relic->category, Relic::CATEGORIES);
        if ($relic->isEquipment()) {
            $this->assertNotNull($relic->item_key);
        }
    }

    public function testGrantRefusesBots(): void
    {
        $this->dominion->user_id = null;

        $this->assertNull($this->relicService->grant($this->dominion, Relic::SOURCE_SEARCH));
    }

    public function testGrantRefusesDuringRoundLock(): void
    {
        $this->round->start_date = now()->subHours(48);
        $this->round->save();
        $this->dominion->setRelation('round', $this->round);

        $this->assertNull($this->relicService->grant($this->dominion, Relic::SOURCE_SEARCH));
    }

    public function testGrantRefusesAFullInventory(): void
    {
        Relic::factory()->ownedBy($this->dominion)->count(config('relics.inventory_cap'))->create();

        $this->assertNull($this->relicService->grant($this->dominion, Relic::SOURCE_SEARCH));
    }

    public function testUpgradeRollRaisesOneTier(): void
    {
        $this->mockRandomChance(true);

        $this->assertEquals('rare', $this->relicService->rollUpgrade('uncommon'));
        $this->assertEquals('legendary', $this->relicService->rollUpgrade('legendary'));
    }

    public function testInfoOpMissAddsProgressPerDominion(): void
    {
        $other = $this->createDominionWithLegacyStats($this->createUser(), $this->round);

        $this->relicService->attemptInfoOpFind($this->dominion, $this->target);
        $this->relicService->attemptInfoOpFind($this->dominion, $other);

        $this->assertEquals(2, RelicTracking::where('dominion_id', $this->dominion->id)->value('progress'));
        $this->assertEquals(0, $this->dominion->relics()->count());
    }

    public function testInfoOpFindIgnoresBotTargets(): void
    {
        $this->mockRandomChance(true);
        $this->target->user_id = null;

        $this->assertSame('', $this->relicService->attemptInfoOpFind($this->dominion, $this->target));
        $this->assertEquals(0, $this->dominion->relics()->count());
    }

    public function testInfoOpFindFillingInventoryTurnsSearchOff(): void
    {
        $this->mockRandomChance(true);
        $this->enableSearch($this->dominion);
        $this->dominion->save();
        Relic::factory()->ownedBy($this->dominion)->count(config('relics.inventory_cap') - 1)->create();

        $message = $this->relicService->attemptInfoOpFind($this->dominion, $this->target);

        $this->assertNotSame('', $message);
        $this->assertEquals(0, $this->dominion->getSetting('relics.search_intensity'));
    }

    public function testPlunderNeedsAttacksEnabledAndRange(): void
    {
        $this->mockRandomChance(true);

        $this->assertNull($this->relicService->attemptPlunder($this->dominion, $this->target, 100));

        config(['relics.sources.attacks' => true]);

        $this->assertNull($this->relicService->attemptPlunder($this->dominion, $this->target, 74));
        $relic = $this->relicService->attemptPlunder($this->dominion, $this->target, 75);

        $this->assertNotNull($relic);
        $this->assertEquals(Relic::SOURCE_PLUNDER, $relic->source);
    }

    public function testCompleteSearchCarriesOverflowAndKeepsSearching(): void
    {
        $this->enableSearch($this->dominion, 2, false);
        $this->dominion->relic_search_meter = 103.5;
        $this->dominion->save();

        $result = $this->relicService->completeSearch($this->dominion);

        $this->assertNotNull($result['relic']);
        $this->assertFalse($result['searchStopped']);
        $this->assertEqualsWithDelta(3.5, $this->dominion->fresh()->relic_search_meter, 0.001);
        $this->assertEquals(2, $this->dominion->fresh()->getSetting('relics.search_intensity'));
    }

    public function testCompleteSearchStopsOnFindWhenAsked(): void
    {
        $this->enableSearch($this->dominion, 1, true);
        $this->dominion->relic_search_meter = 100;
        $this->dominion->save();

        $result = $this->relicService->completeSearch($this->dominion);

        $this->assertTrue($result['searchStopped']);
        $this->assertEquals(0, $this->dominion->fresh()->getSetting('relics.search_intensity'));
    }

    public function testCompleteSearchWithFullInventoryKeepsMeterFull(): void
    {
        $this->enableSearch($this->dominion);
        $this->dominion->relic_search_meter = 120;
        $this->dominion->save();
        Relic::factory()->ownedBy($this->dominion)->count(config('relics.inventory_cap'))->create();

        $result = $this->relicService->completeSearch($this->dominion);

        $this->assertNull($result['relic']);
        $this->assertTrue($result['searchStopped']);
        $this->assertEquals(100, $this->dominion->fresh()->relic_search_meter);
    }

    public function testTransferUnbindsUnlistsAndResetsClock(): void
    {
        $relic = Relic::factory()->ownedBy($this->target)->listed()->create([
            'is_bound' => true,
            'acquired_at' => now()->subHours(50),
        ]);

        $this->relicService->transfer($relic, $this->dominion, Relic::SOURCE_STOLEN);

        $relic->refresh();
        $this->assertEquals($this->dominion->id, $relic->dominion_id);
        $this->assertEquals($this->target->id, $relic->previous_owner_dominion_id);
        $this->assertFalse($relic->is_bound);
        $this->assertFalse($relic->is_listed);
        $this->assertTrue($relic->acquired_at->gt(now()->subMinute()));
    }

    public function testPickStealableNeverPicksBoundOrSoldRelics(): void
    {
        Relic::factory()->ownedBy($this->target)->bound()->create();
        Relic::factory()->ownedBy($this->target)->sold(5000)->create();

        $this->assertNull($this->relicService->pickStealable($this->target));

        $unbound = Relic::factory()->ownedBy($this->target)->create();
        $this->assertEquals($unbound->id, $this->relicService->pickStealable($this->target)->id);
    }
}
