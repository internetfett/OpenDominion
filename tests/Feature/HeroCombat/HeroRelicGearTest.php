<?php

namespace OpenDominion\Tests\Feature\HeroCombat;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OpenDominion\Calculators\Dominion\HeroCalculator;
use OpenDominion\Exceptions\GameException;
use OpenDominion\Helpers\RelicHelper;
use OpenDominion\HeroCombat\Content\Loadouts\LoadoutSlot;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Hero;
use OpenDominion\Models\Relic;
use OpenDominion\Models\Round;
use OpenDominion\Services\Dominion\Actions\HeroActionService;
use OpenDominion\Tests\AbstractBrowserKitTestCase;
use OpenDominion\Tests\Traits\EnablesFullHeroProgression;

class HeroRelicGearTest extends AbstractBrowserKitTestCase
{
    use DatabaseTransactions;
    use EnablesFullHeroProgression;

    protected Round $round;

    protected HeroActionService $heroActionService;

    protected HeroCalculator $heroCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enableFullHeroProgression();

        $this->round = $this->createRound('-5 days midnight');
        $this->heroActionService = $this->app->make(HeroActionService::class);
        $this->heroCalculator = $this->app->make(HeroCalculator::class);
    }

    protected function dominionWithHero(float $experience = 1200): Dominion
    {
        $dominion = $this->createDominionWithLegacyStats($this->createUser(), $this->round);

        Hero::create([
            'dominion_id' => $dominion->id,
            'name' => 'Relic Hero',
            'class' => 'blacksmith',
            'experience' => $experience,
        ]);

        return $dominion->refresh();
    }

    /**
     * An equipment item from the data file that raises a single stat, with no drawback.
     *
     * @return array{key: string, rarity: string, stats: array<string, int>}
     */
    protected function equipmentItem(): array
    {
        return collect(app(RelicHelper::class)->getEquipment())
            ->first(fn (array $item) => count($item['stats']) === 1 && array_values($item['stats'])[0] > 0);
    }

    protected function equipmentRelic(Dominion $dominion): Relic
    {
        $item = $this->equipmentItem();

        return Relic::factory()->ownedBy($dominion)->equipment($item['key'], $item['rarity'])->create();
    }

    public function testEquipmentRelicIsAvailableGear(): void
    {
        $dominion = $this->dominionWithHero();
        $relic = $this->equipmentRelic($dominion);
        Relic::factory()->ownedBy($dominion)->create();

        $available = $this->heroCalculator->getAvailableLoadoutOptions($dominion->hero, LoadoutSlot::Gear);

        $this->assertArrayHasKey($relic->loadoutKey(), $available);
        $this->assertCount(1, array_filter(array_keys($available), fn (string $key) => str_starts_with($key, Relic::LOADOUT_KEY_PREFIX)));
    }

    public function testEquipmentRelicNeedsTheFirstGearSlot(): void
    {
        $dominion = $this->dominionWithHero(0);
        $relic = $this->equipmentRelic($dominion);

        $this->assertArrayNotHasKey($relic->loadoutKey(), $this->heroCalculator->getAvailableLoadoutOptions($dominion->hero, LoadoutSlot::Gear));
    }

    public function testEquippingBindsTheRelicAndAppliesItsStats(): void
    {
        $dominion = $this->dominionWithHero();
        $relic = $this->equipmentRelic($dominion);
        $item = $this->equipmentItem();
        $stat = array_key_first($item['stats']);
        $baseStat = $this->heroCalculator->getHeroCombatStats($dominion->hero)[$stat];

        $this->heroActionService->updateLoadout($dominion, null, null, [$relic->loadoutKey()]);

        $hero = $dominion->hero->fresh();
        $this->assertTrue($relic->refresh()->is_bound);
        $this->assertEquals($baseStat + $item['stats'][$stat], $this->heroCalculator->getHeroCombatStats($hero)[$stat]);
    }

    public function testUnequippingKeepsTheRelicBound(): void
    {
        $dominion = $this->dominionWithHero();
        $relic = $this->equipmentRelic($dominion);

        $this->heroActionService->updateLoadout($dominion, null, null, [$relic->loadoutKey()]);
        $this->heroActionService->updateLoadout($dominion->fresh(), null, null, []);

        $this->assertTrue($relic->refresh()->is_bound);
    }

    public function testListedRelicCannotBeEquipped(): void
    {
        $dominion = $this->dominionWithHero();
        $relic = $this->equipmentRelic($dominion);
        $relic->update(['is_listed' => true]);

        $this->expectException(GameException::class);

        $this->heroActionService->updateLoadout($dominion, null, null, [$relic->loadoutKey()]);
    }

    public function testAnotherDominionsRelicCannotBeEquipped(): void
    {
        $dominion = $this->dominionWithHero();
        $relic = $this->equipmentRelic($this->dominionWithHero());

        $this->expectException(GameException::class);

        $this->heroActionService->updateLoadout($dominion, null, null, [$relic->loadoutKey()]);
    }

    public function testRelicThatLeavesInventoryDropsOutOfTheLoadout(): void
    {
        $dominion = $this->dominionWithHero();
        $relic = $this->equipmentRelic($dominion);
        $this->heroActionService->updateLoadout($dominion, null, null, [$relic->loadoutKey()]);

        $relic->update(['sold_at' => now(), 'sold_price' => 1]);

        $hero = Hero::find($dominion->hero->id);
        $this->assertSame([], $this->heroCalculator->getLoadout($hero)[LoadoutSlot::Gear->value]);
    }
}
