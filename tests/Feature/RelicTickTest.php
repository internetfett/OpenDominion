<?php

namespace OpenDominion\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OpenDominion\Calculators\Dominion\RelicCalculator;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Relic;
use OpenDominion\Models\Round;
use OpenDominion\Services\Dominion\TickService;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class RelicTickTest extends AbstractBrowserKitTestCase
{
    use DatabaseTransactions;

    protected Round $round;

    protected Dominion $dominion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->round = $this->createRound('-7 days');
        $this->dominion = $this->createDominionWithLegacyStats($this->createUser(), $this->round);
        $this->dominion->military_spies = 1000;
        $this->dominion->spy_strength = 100;
        $this->dominion->save();
    }

    protected function searchAt(int $intensity, bool $stopOnFind = false): void
    {
        $this->dominion->settings = ['relics' => ['search_intensity' => $intensity, 'search_stop_on_find' => $stopOnFind]];
        $this->dominion->save();
    }

    public function testSearchDrainsStrengthEvenAtFullStrength(): void
    {
        $this->searchAt(2);

        app(TickService::class)->performTick($this->round);

        $this->assertEquals(98, $this->dominion->fresh()->spy_strength);
    }

    public function testSearchingDominionSettlesBelowFullStrength(): void
    {
        $this->searchAt(2);
        $tickService = app(TickService::class);

        $tickService->performTick($this->round);
        $tickService->performTick($this->round);

        $this->assertEquals(98, $this->dominion->fresh()->spy_strength);
    }

    public function testMeterFillsByTheCalculatedProgress(): void
    {
        $this->searchAt(1);
        $expected = app(RelicCalculator::class)->getSearchProgress($this->dominion->fresh());

        app(TickService::class)->performTick($this->round);

        $this->assertGreaterThan(0, $expected);
        $this->assertEqualsWithDelta(min($expected, 100), $this->dominion->fresh()->relic_search_meter, 0.01);
    }

    public function testFullMeterFindsARelicAtTheTick(): void
    {
        $this->searchAt(2, true);
        $this->dominion->relic_search_meter = 99.99;
        $this->dominion->save();

        app(TickService::class)->performTick($this->round);

        $dominion = $this->dominion->fresh();
        $relic = $dominion->relics()->first();
        $this->assertNotNull($relic);
        $this->assertEquals(Relic::SOURCE_SEARCH, $relic->source);
        $this->assertLessThan(100, $dominion->relic_search_meter);
        $this->assertEquals(0, $dominion->getSetting('relics.search_intensity'));
    }

    public function testSearchOffLeavesStrengthAndMeterAlone(): void
    {
        $this->dominion->spy_strength = 90;
        $this->dominion->save();

        app(TickService::class)->performTick($this->round);

        $dominion = $this->dominion->fresh();
        $this->assertGreaterThan(90, $dominion->spy_strength);
        $this->assertEquals(0, $dominion->relic_search_meter);
    }
}
