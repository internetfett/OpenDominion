<?php

namespace OpenDominion\Tests\Unit\Helpers;

use OpenDominion\Helpers\RelicHelper;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\Models\Relic;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class RelicHelperTest extends AbstractBrowserKitTestCase
{
    public function testGeneratedCategoriesAreTheSaleOnlyCategories(): void
    {
        $this->assertEqualsCanonicalizing(
            array_values(array_diff(Relic::CATEGORIES, [Relic::CATEGORY_EQUIPMENT])),
            app(RelicHelper::class)->getGeneratedCategories()
        );
    }

    public function testEveryGeneratedCategoryNamesEveryRarity(): void
    {
        $relicHelper = app(RelicHelper::class);

        foreach ($relicHelper->getGeneratedCategories() as $category) {
            foreach (array_keys($relicHelper->getRarities()) as $rarity) {
                $this->assertNotEmpty($relicHelper->generateName($category, $rarity), "{$category} {$rarity}");
            }
        }
    }

    public function testEveryRarityHasEquipmentWithKnownStats(): void
    {
        $relicHelper = app(RelicHelper::class);
        $stats = array_map(fn (Stat $stat) => $stat->value, Stat::cases());

        foreach (array_keys($relicHelper->getRarities()) as $rarity) {
            $this->assertNotEmpty($relicHelper->getEquipmentOfRarity($rarity), $rarity);
        }

        foreach ($relicHelper->getEquipment() as $key => $item) {
            $this->assertNotEmpty($item['stats'], $key);
            $this->assertEmpty(array_diff(array_keys($item['stats']), $stats), $key);
        }
    }

    public function testNextRarityStopsAtTheTopTier(): void
    {
        $relicHelper = app(RelicHelper::class);

        $this->assertEquals('uncommon', $relicHelper->getNextRarity('common'));
        $this->assertEquals('legendary', $relicHelper->getNextRarity('legendary'));
    }
}
