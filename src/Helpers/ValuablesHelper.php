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
                'discovery_chance' => 0.15, // 15% chance
            ],
            [
                'key' => 'uncommon',
                'name' => 'Uncommon',
                'base_value_min' => 10000,
                'base_value_max' => 25000,
                'discovery_chance' => 0.10, // 10% chance
            ],
            [
                'key' => 'rare',
                'name' => 'Rare',
                'base_value_min' => 25000,
                'base_value_max' => 50000,
                'discovery_chance' => 0.05, // 5% chance
            ],
            [
                'key' => 'epic',
                'name' => 'Epic',
                'base_value_min' => 50000,
                'base_value_max' => 100000,
                'discovery_chance' => 0.02, // 2% chance
            ],
            [
                'key' => 'legendary',
                'name' => 'Legendary',
                'base_value_min' => 100000,
                'base_value_max' => 250000,
                'discovery_chance' => 0.005, // 0.5% chance
            ],
        ]);
    }

    public function getValuableRarityInfo(string $rarityKey): ?array
    {
        return $this->getValuableRarities()->firstWhere('key', $rarityKey);
    }
}
