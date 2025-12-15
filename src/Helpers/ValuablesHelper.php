<?php

namespace OpenDominion\Helpers;

use Illuminate\Support\Collection;
use OpenDominion\Models\Valuable;

class ValuablesHelper
{
    /**
     * Get display string for a discovered valuable
     *
     * @param Valuable $valuable
     * @return string
     */
    public function getDiscoveryDisplay(Valuable $valuable): string
    {
        // Determine article (a/an) based on rarity
        $article = in_array(strtolower($valuable->rarity[0]), ['a', 'e', 'i', 'o', 'u']) ? 'an' : 'a';

        // Handle type pluralization and special cases
        $typeDisplay = $valuable->type;
        if ($typeDisplay === 'jewelry') {
            $typeDisplay = 'item of jewelry';
        } elseif ($typeDisplay === 'equipment') {
            $typeDisplay = 'piece of equipment';
        } elseif ($typeDisplay === 'artwork') {
            $typeDisplay = 'work of art';
        }

        return sprintf(
            '%s %s %s',
            $article,
            $valuable->rarity,
            $typeDisplay
        );
    }

    public function getValuableTypes(): Collection
    {
        return collect([
            'relic',
            'jewelry',
            'artwork',
            'equipment',
            'text',
        ]);
    }

    public function getValuableRarities(): Collection
    {
        return collect([
            [
                'key' => 'common',
                'name' => 'Common',
                'base_value_min' => 5000,
                'base_value_max' => 10000,
                'spy_hours_multiplier' => 0.5, // Spy-hours = target land × multiplier
            ],
            [
                'key' => 'uncommon',
                'name' => 'Uncommon',
                'base_value_min' => 10000,
                'base_value_max' => 25000,
                'spy_hours_multiplier' => 1.0,
            ],
            [
                'key' => 'rare',
                'name' => 'Rare',
                'base_value_min' => 25000,
                'base_value_max' => 50000,
                'spy_hours_multiplier' => 2.0,
            ],
            [
                'key' => 'epic',
                'name' => 'Epic',
                'base_value_min' => 50000,
                'base_value_max' => 100000,
                'spy_hours_multiplier' => 3.0,
            ],
            [
                'key' => 'legendary',
                'name' => 'Legendary',
                'base_value_min' => 100000,
                'base_value_max' => 250000,
                'spy_hours_multiplier' => 5.0,
            ],
        ]);
    }

    public function getValuableRarityInfo(string $rarityKey): ?array
    {
        return $this->getValuableRarities()->firstWhere('key', $rarityKey);
    }

    /**
     * Calculate required spy-hours based on target's current land
     *
     * @param Valuable $valuable
     * @return int
     */
    public function calculateSpyHours(Valuable $valuable): int
    {
        $rarityInfo = $this->getValuableRarityInfo($valuable->rarity);
        if (!$rarityInfo) {
            return 0;
        }

        $landCalculator = app(\OpenDominion\Calculators\Dominion\LandCalculator::class);
        $targetLand = $landCalculator->getTotalLand($valuable->targetDominion);
        $multiplier = $rarityInfo['spy_hours_multiplier'];

        return (int) round($targetLand * $multiplier);
    }

    /**
     * Get the required spy-hours for a valuable
     * Returns stored value if set, otherwise calculates on-the-fly
     *
     * @param Valuable $valuable
     * @return int
     */
    public function getRequiredSpyHours(Valuable $valuable): int
    {
        if ($valuable->spy_hours !== null) {
            return $valuable->spy_hours;
        }

        return $this->calculateSpyHours($valuable);
    }
}
