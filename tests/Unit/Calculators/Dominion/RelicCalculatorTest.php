<?php

namespace OpenDominion\Tests\Unit\Calculators\Dominion;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Mockery;
use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Calculators\Dominion\RelicCalculator;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Relic;
use OpenDominion\Models\Round;
use OpenDominion\Tests\AbstractBrowserKitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RelicCalculatorTest extends AbstractBrowserKitTestCase
{
    use DatabaseTransactions;

    protected Round $round;

    protected Dominion $dominion;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(now()->startOfHour());

        $user = $this->createUser();
        $this->round = $this->createRound('-5 days midnight');
        $this->dominion = $this->createDominionWithLegacyStats($user, $this->round);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function calculatorWithSpyRatio(float $spyRatio): RelicCalculator
    {
        $militaryCalculator = Mockery::mock(MilitaryCalculator::class)->makePartial();
        $militaryCalculator->shouldReceive('getSpyRatio')->andReturn($spyRatio);
        $this->app->instance(MilitaryCalculator::class, $militaryCalculator);

        return app(RelicCalculator::class);
    }

    public static function marketPriceProvider(): array
    {
        return [
            'just found' => [0, 25000],
            'half way' => [36, 37500],
            'fully appreciated' => [72, 50000],
            'holds at max' => [100, 50000],
        ];
    }

    #[DataProvider('marketPriceProvider')]
    public function testMarketPriceClimbsFromMinToMax(int $hoursHeld, int $expectedPrice): void
    {
        $relic = Relic::factory()->ownedBy($this->dominion)->create([
            'rarity' => 'rare',
            'acquired_at' => now()->subHours($hoursHeld),
        ]);

        $this->assertEquals($expectedPrice, app(RelicCalculator::class)->getMarketPrice($relic));
    }

    public function testRealmPriceIsRarityMinimumAndSalvageIsAQuarterOfIt(): void
    {
        $calculator = app(RelicCalculator::class);

        $this->assertEquals(50000, $calculator->getRealmPrice('legendary'));
        $this->assertEquals(12500, $calculator->getSalvageValue('legendary'));
    }

    public static function searchSpeedProvider(): array
    {
        return [
            '0.40 SPA at 2%' => [0.40, 2, 120],
            '1.00 SPA at 2%' => [1.00, 2, 24],
            '1.00 SPA at 1%' => [1.00, 1, 48],
        ];
    }

    #[DataProvider('searchSpeedProvider')]
    public function testHoursUntilFindScaleWithSpyRatioAndIntensity(float $spyRatio, int $intensity, int $expectedHours): void
    {
        $this->dominion->settings = ['relics' => ['search_intensity' => $intensity]];

        $this->assertEquals($expectedHours, $this->calculatorWithSpyRatio($spyRatio)->getHoursUntilFind($this->dominion));
    }

    public function testSearchMakesNoProgressAtOrBelowThreshold(): void
    {
        $this->dominion->settings = ['relics' => ['search_intensity' => 2]];

        $calculator = $this->calculatorWithSpyRatio(0.25);

        $this->assertNull($calculator->getHoursUntilFind($this->dominion));
        $this->assertEquals(0, $calculator->getSearchProgress($this->dominion));
    }

    public function testSearchDrainsItsIntensity(): void
    {
        $this->dominion->settings = ['relics' => ['search_intensity' => 2]];
        $this->dominion->spy_strength = 100;

        $calculator = $this->calculatorWithSpyRatio(1.0);

        $this->assertEquals(2, $calculator->getSearchDrain($this->dominion));
        $this->assertEqualsWithDelta(4.2, $calculator->getSearchProgress($this->dominion), 0.0001);
    }

    public function testSearchDrainIsZeroWhenOff(): void
    {
        $this->assertEquals(0, app(RelicCalculator::class)->getSearchDrain($this->dominion));
    }

    public function testSearchDrainIsZeroWithInvalidIntensity(): void
    {
        $this->dominion->settings = ['relics' => ['search_intensity' => 5]];

        $this->assertEquals(0, app(RelicCalculator::class)->getSearchDrain($this->dominion));
    }

    public function testSearchDrainIsZeroBelowDrainStrength(): void
    {
        $this->dominion->settings = ['relics' => ['search_intensity' => 2]];
        $this->dominion->spy_strength = 1.5;

        $calculator = $this->calculatorWithSpyRatio(1.0);

        $this->assertEquals(0, $calculator->getSearchDrain($this->dominion));
        $this->assertEquals(0, $calculator->getSearchProgress($this->dominion));
    }

    public function testSearchDrainIsZeroUnderProtection(): void
    {
        $this->dominion->settings = ['relics' => ['search_intensity' => 2]];
        $this->dominion->protection_finished = false;

        $this->assertEquals(0, app(RelicCalculator::class)->getSearchDrain($this->dominion));
    }

    public function testSearchDrainIsZeroDuringRoundLock(): void
    {
        $this->round->start_date = now()->subHours(71);
        $this->round->save();
        $this->dominion->setRelation('round', $this->round);
        $this->dominion->settings = ['relics' => ['search_intensity' => 2]];

        $this->assertEquals(0, app(RelicCalculator::class)->getSearchDrain($this->dominion));
    }

    public static function rarityProvider(): array
    {
        return [
            'small, no spies' => [350, 0.0, 'common'],
            'specialist at start' => [600, 1.0, 'rare'],
            'specialist just short of legendary' => [3700, 1.0, 'epic'],
            'specialist at legendary land' => [3750, 1.0, 'legendary'],
            'spy ratio is capped at 1' => [350, 3.0, 'rare'],
        ];
    }

    #[DataProvider('rarityProvider')]
    public function testRarityBandsFromLandAndSpyRatio(int $land, float $spyRatio, string $expectedRarity): void
    {
        $calculator = app(RelicCalculator::class);

        $this->assertEquals($expectedRarity, $calculator->getRarityForScore($calculator->getRarityScore($land, $spyRatio)));
    }

    public function testInventoryFullCountsHeldRelicsOnly(): void
    {
        Relic::factory()->ownedBy($this->dominion)->count(7)->create();
        Relic::factory()->ownedBy($this->dominion)->sold(5000)->create();

        $calculator = app(RelicCalculator::class);
        $this->assertFalse($calculator->isInventoryFull($this->dominion));

        Relic::factory()->ownedBy($this->dominion)->bound()->create();
        $this->assertTrue($calculator->isInventoryFull($this->dominion));
    }

    public function testHighAlertLastsTwelveHoursAfterATheft(): void
    {
        $thief = $this->createDominionWithLegacyStats($this->createUser(), $this->round);
        $relic = Relic::factory()->ownedBy($thief)->create([
            'source' => Relic::SOURCE_STOLEN,
            'previous_owner_dominion_id' => $this->dominion->id,
            'acquired_at' => now()->subHours(11),
        ]);

        $calculator = app(RelicCalculator::class);
        $this->assertTrue($calculator->isOnHighAlert($this->dominion));
        $this->assertFalse($calculator->isOnHighAlert($thief));

        $relic->update(['acquired_at' => now()->subHours(12)]);
        $this->assertFalse($calculator->isOnHighAlert($this->dominion));
    }

    public function testPurchaseDoesNotRaiseHighAlert(): void
    {
        $buyer = $this->createDominionWithLegacyStats($this->createUser(), $this->round);
        Relic::factory()->ownedBy($buyer)->create([
            'source' => Relic::SOURCE_PURCHASED,
            'previous_owner_dominion_id' => $this->dominion->id,
        ]);

        $this->assertFalse(app(RelicCalculator::class)->isOnHighAlert($this->dominion));
    }
}
