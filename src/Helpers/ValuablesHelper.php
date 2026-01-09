<?php

namespace OpenDominion\Helpers;

use Illuminate\Support\Collection;
use OpenDominion\Models\Valuable;

class ValuablesHelper
{
    // Valuable expiration and timing constants
    const EXPIRATION_HOURS = 48;
    const MIN_INVESTIGATION_HOURS = 36;  // Slowest option (least spies)
    const MAX_INVESTIGATION_HOURS = 6;   // Fastest option (most spies)
    const INVESTIGATION_HOUR_STEP = 6;   // UI hour increment

    // Discovery and pricing constants
    const DISCOVERY_CHANCE = 0.01; // 1%
    const PRICE_VOLATILITY = 0.1;   // 10%

    // Spy strength cost constant
    const SPY_STRENGTH_PER_INVESTIGATION = 2.0; // 2% per spy per hour

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
                'transfer_price' => 2500,
            ],
            [
                'key' => 'uncommon',
                'name' => 'Uncommon',
                'base_value_min' => 10000,
                'base_value_max' => 25000,
                'spy_hours_multiplier' => 1.0,
                'transfer_price' => 5000,
            ],
            [
                'key' => 'rare',
                'name' => 'Rare',
                'base_value_min' => 25000,
                'base_value_max' => 50000,
                'spy_hours_multiplier' => 2.0,
                'transfer_price' => 10000,
            ],
            [
                'key' => 'epic',
                'name' => 'Epic',
                'base_value_min' => 50000,
                'base_value_max' => 100000,
                'spy_hours_multiplier' => 3.0,
                'transfer_price' => 20000,
            ],
            [
                'key' => 'legendary',
                'name' => 'Legendary',
                'base_value_min' => 100000,
                'base_value_max' => 250000,
                'spy_hours_multiplier' => 5.0,
                'transfer_price' => 40000,
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

    /**
     * Get ticks remaining until valuable expires
     *
     * @param Valuable $valuable
     * @return int
     */
    public function getTicksUntilExpiration(Valuable $valuable): int
    {
        if ($valuable->created_at === null) {
            return 0;
        }

        $expiresAt = $valuable->created_at->copy()->addHours(self::EXPIRATION_HOURS);
        return max(0, now()->diffInHours($expiresAt, false));
    }

    /**
     * Get transfer price for a valuable based on rarity
     *
     * @param Valuable $valuable
     * @return int
     */
    public function getTransferPrice(Valuable $valuable): int
    {
        $rarityInfo = $this->getValuableRarityInfo($valuable->rarity);

        if (!$rarityInfo || !isset($rarityInfo['transfer_price'])) {
            // Fallback to common price
            $commonInfo = $this->getValuableRarityInfo('common');
            return $commonInfo['transfer_price'] ?? 2500;
        }

        return $rarityInfo['transfer_price'];
    }

    /**
     * Calculate total spy strength recovery cost for an investigation duration
     * Each investigation costs a flat 2% per hour ongoing drain on spy strength recovery
     * This method returns the total spy strength lost over the investigation period
     *
     * @param int $investigationHours
     * @return float Total spy strength cost over duration (e.g., 6 hours = 12%, 36 hours = 72%)
     */
    public function getInvestigationSpyStrengthCost(int $investigationHours): float
    {
        return $investigationHours * self::SPY_STRENGTH_PER_INVESTIGATION;
    }
}
