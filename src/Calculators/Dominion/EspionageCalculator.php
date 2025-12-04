<?php

namespace OpenDominion\Calculators\Dominion;

use OpenDominion\Helpers\EspionageHelper;
use OpenDominion\Helpers\ValuablesHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Valuable;

class EspionageCalculator
{
    /** @var EspionageHelper */
    protected $espionageHelper;

    /** @var ValuablesHelper */
    protected $valuablesHelper;

    /**
     * EspionageCalculator constructor.
     *
     * @param EspionageHelper $espionageHelper
     * @param ValuablesHelper $valuablesHelper
     */
    public function __construct(EspionageHelper $espionageHelper, ValuablesHelper $valuablesHelper)
    {
        $this->espionageHelper = $espionageHelper;
        $this->valuablesHelper = $valuablesHelper;
    }

    public function canPerform(Dominion $dominion, string $operation): bool
    {
        $spyStrengthCost = 5;

        if ($this->espionageHelper->isInfoGatheringOperation($operation)) {
            $spyStrengthCost = 2;
        }

        return ($dominion->spy_strength >= 30);
    }

    /**
     * Calculate the sell price of a valuable using a random walk
     *
     * Uses the valuable's ID as a random seed and hours since theft as iterations
     * to create a deterministic but fluctuating price within the rarity's min/max bounds
     *
     * @param Valuable $valuable
     * @param int $hoursAgo Number of hours in the past to check (default: 0 for current)
     * @return int
     */
    public function getValuableSellPrice(Valuable $valuable, int $hoursAgo = 0): int
    {
        if (!$valuable->attempted_at) {
            return 0;
        }

        $rarityInfo = $this->valuablesHelper->getValuableRarityInfo($valuable->rarity);
        if (!$rarityInfo) {
            return 0;
        }

        $minValue = $rarityInfo['base_value_min'];
        $maxValue = $rarityInfo['base_value_max'];

        // Start at the midpoint
        $currentPrice = ($minValue + $maxValue) / 2;

        // Calculate hours since theft, adjusted for looking into the past
        $hoursSinceTheft = now()->diffInHours($valuable->attempted_at) - $hoursAgo;

        // Can't look further back than the theft itself
        if ($hoursSinceTheft < 0) {
            $hoursSinceTheft = 0;
        }

        // Use valuable ID as seed for deterministic randomness
        mt_srand($valuable->id);

        // Random walk: each hour the price moves up or down
        // Step size is proportional to the range
        $stepSize = ($maxValue - $minValue) * 0.05; // 5% of range per step

        for ($i = 0; $i < $hoursSinceTheft; $i++) {
            // Random step: -1 or +1
            $direction = (mt_rand(0, 1) * 2) - 1;
            $currentPrice += $direction * $stepSize;

            // Keep within bounds
            $currentPrice = max($minValue, min($maxValue, $currentPrice));
        }

        // Reset the random seed to avoid affecting other random operations
        mt_srand();

        return (int) round($currentPrice);
    }
}
