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
                'spy_hours' => 12, // Hours of spy investigation to reach max success
            ],
            [
                'key' => 'uncommon',
                'name' => 'Uncommon',
                'base_value_min' => 10000,
                'base_value_max' => 25000,
                'discovery_chance' => 0.10, // 10% chance
                'spy_hours' => 24,
            ],
            [
                'key' => 'rare',
                'name' => 'Rare',
                'base_value_min' => 25000,
                'base_value_max' => 50000,
                'discovery_chance' => 0.05, // 5% chance
                'spy_hours' => 48,
            ],
            [
                'key' => 'epic',
                'name' => 'Epic',
                'base_value_min' => 50000,
                'base_value_max' => 100000,
                'discovery_chance' => 0.02, // 2% chance
                'spy_hours' => 72,
            ],
            [
                'key' => 'legendary',
                'name' => 'Legendary',
                'base_value_min' => 100000,
                'base_value_max' => 250000,
                'discovery_chance' => 0.005, // 0.5% chance
                'spy_hours' => 120,
            ],
        ]);
    }

    public function getValuableRarityInfo(string $rarityKey): ?array
    {
        return $this->getValuableRarities()->firstWhere('key', $rarityKey);
    }

    /**
     * Calculate theft success chance based on investigation progress
     *
     * @param Valuable $valuable
     * @return float Success chance (0.0 to 1.0)
     */
    public function getTheftSuccessChance(Valuable $valuable): float
    {
        if (!$valuable->investigation_started_at || $valuable->spies_assigned === 0) {
            return 0.0;
        }

        $rarityInfo = $this->getValuableRarityInfo($valuable->rarity);
        if (!$rarityInfo) {
            return 0.0;
        }

        $spyHours = $rarityInfo['spy_hours'];
        $hoursInvestigated = now()->diffInHours($valuable->investigation_started_at);

        // Progress = (spies_assigned * hours_investigated) / spy_hours
        // Capped at 1.0 (100%)
        $progress = min(1.0, ($valuable->spies_assigned * $hoursInvestigated) / $spyHours);

        return $progress;
    }

    /**
     * Get the required spy-hours for a valuable's rarity
     *
     * @param Valuable $valuable
     * @return int
     */
    public function getRequiredSpyHours(Valuable $valuable): int
    {
        $rarityInfo = $this->getValuableRarityInfo($valuable->rarity);
        return $rarityInfo['spy_hours'] ?? 0;
    }
}
