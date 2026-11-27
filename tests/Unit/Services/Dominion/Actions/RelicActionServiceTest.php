<?php

namespace OpenDominion\Tests\Unit\Services\Dominion\Actions;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Notification;
use OpenDominion\Exceptions\GameException;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Relic;
use OpenDominion\Models\Round;
use OpenDominion\Notifications\WebNotification;
use OpenDominion\Services\Dominion\Actions\RelicActionService;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class RelicActionServiceTest extends AbstractBrowserKitTestCase
{
    use DatabaseTransactions;

    protected Round $round;

    protected Dominion $dominion;

    protected Dominion $realmmate;

    protected RelicActionService $relicActionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->round = $this->createRound('-5 days midnight');
        $this->dominion = $this->createDominionWithLegacyStats($this->createUser(), $this->round);
        $this->realmmate = $this->createDominionWithLegacyStats($this->createUser(), $this->round, null, $this->dominion->realm);
        $this->relicActionService = app(RelicActionService::class);
    }

    public function testSellPaysCurrentValueAndKeepsHistory(): void
    {
        $relic = Relic::factory()->ownedBy($this->dominion)->create(['rarity' => 'rare', 'acquired_at' => now()->subHours(80)]);
        $platinumBefore = $this->dominion->resource_platinum;

        $this->relicActionService->sell($this->dominion, $relic);

        $relic->refresh();
        $this->assertEquals($platinumBefore + 50000, $this->dominion->resource_platinum);
        $this->assertEquals(50000, $relic->sold_price);
        $this->assertFalse($relic->isHeld());
    }

    public function testSellRefusesBoundRelics(): void
    {
        $relic = Relic::factory()->ownedBy($this->dominion)->bound()->create();

        $this->expectException(GameException::class);

        $this->relicActionService->sell($this->dominion, $relic);
    }

    public function testSellRefusesSomeoneElsesRelic(): void
    {
        $relic = Relic::factory()->ownedBy($this->realmmate)->create();

        $this->expectException(GameException::class);

        $this->relicActionService->sell($this->dominion, $relic);
    }

    public function testSalvagePaysAQuarterOfMinimumForBoundRelics(): void
    {
        $relic = Relic::factory()->ownedBy($this->dominion)->bound()->create(['rarity' => 'legendary']);
        $platinumBefore = $this->dominion->resource_platinum;

        $this->relicActionService->salvage($this->dominion, $relic);

        $relic->refresh();
        $this->assertEquals($platinumBefore + 12500, $this->dominion->resource_platinum);
        $this->assertTrue($relic->wasSalvaged());
    }

    public function testSalvageRefusesUnboundRelics(): void
    {
        $relic = Relic::factory()->ownedBy($this->dominion)->create();

        $this->expectException(GameException::class);

        $this->relicActionService->salvage($this->dominion, $relic);
    }

    public function testListRefusesBoundRelics(): void
    {
        $relic = Relic::factory()->ownedBy($this->dominion)->bound()->create();

        $this->expectException(GameException::class);

        $this->relicActionService->list($this->dominion, $relic);
    }

    public function testListAndUnlist(): void
    {
        $relic = Relic::factory()->ownedBy($this->dominion)->create();

        $this->relicActionService->list($this->dominion, $relic);
        $this->assertTrue($relic->refresh()->is_listed);

        $this->relicActionService->unlist($this->dominion, $relic);
        $this->assertFalse($relic->refresh()->is_listed);
    }

    public function testPurchasePaysMinimumAndMovesTheRelic(): void
    {
        $relic = Relic::factory()->ownedBy($this->realmmate)->listed()->create(['rarity' => 'epic', 'acquired_at' => now()->subHours(60)]);
        $buyerPlatinum = $this->dominion->resource_platinum;
        $sellerPlatinum = $this->realmmate->resource_platinum;

        $this->relicActionService->purchase($this->dominion, $relic);

        $relic->refresh();
        $this->assertEquals($buyerPlatinum - 37500, $this->dominion->resource_platinum);
        $this->assertEquals($sellerPlatinum + 37500, $this->realmmate->fresh()->resource_platinum);
        $this->assertEquals($this->dominion->id, $relic->dominion_id);
        $this->assertEquals(Relic::SOURCE_PURCHASED, $relic->source);
        $this->assertFalse($relic->is_listed);
        $this->assertTrue($relic->acquired_at->gt(now()->subMinute()));
        Notification::assertSentTo($this->realmmate, WebNotification::class);
    }

    public function testPurchaseRefusesAFullInventory(): void
    {
        Relic::factory()->ownedBy($this->dominion)->count(config('relics.inventory_cap'))->create();
        $relic = Relic::factory()->ownedBy($this->realmmate)->listed()->create();

        $this->expectException(GameException::class);

        $this->relicActionService->purchase($this->dominion, $relic);
    }

    public function testPurchaseRefusesOtherRealms(): void
    {
        $stranger = $this->createDominionWithLegacyStats($this->createUser(), $this->round, null, $this->createRealm($this->round));
        $relic = Relic::factory()->ownedBy($stranger)->listed()->create();

        $this->expectException(GameException::class);

        $this->relicActionService->purchase($this->dominion, $relic);
    }

    public function testPurchaseRefusesUnlistedRelics(): void
    {
        $relic = Relic::factory()->ownedBy($this->realmmate)->create();

        $this->expectException(GameException::class);

        $this->relicActionService->purchase($this->dominion, $relic);
    }

    public function testUpdateSearchSettings(): void
    {
        $this->relicActionService->updateSearchSettings($this->dominion, 2, false);

        $dominion = $this->dominion->fresh();
        $this->assertEquals(2, $dominion->getSetting('relics.search_intensity'));
        $this->assertFalse($dominion->getSetting('relics.search_stop_on_find'));
    }

    public function testUpdateSearchSettingsRefusesAFullInventory(): void
    {
        Relic::factory()->ownedBy($this->dominion)->count(config('relics.inventory_cap'))->create();

        $this->expectException(GameException::class);

        $this->relicActionService->updateSearchSettings($this->dominion, 1, true);
    }

    public function testUpdateSearchSettingsRefusesInvalidIntensity(): void
    {
        $this->expectException(GameException::class);

        $this->relicActionService->updateSearchSettings($this->dominion, 3, true);
    }
}
