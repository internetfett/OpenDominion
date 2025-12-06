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
     * Calculate the sell price of a valuable using a random walk algorithm.
     *
     * When $hoursAgo is 0, returns the current price as an integer.
     * When $hoursAgo > 0, returns an array of prices from $hoursAgo down to 0.
     *
     * @param Valuable $valuable
     * @param int $hoursAgo Number of hours of price history to return (0 = current price only)
     * @return int|array Returns int when $hoursAgo=0, array otherwise
     */
    public function getValuableSellPrice(Valuable $valuable, int $hoursAgo = 0)
    {
        if (!$valuable->attempted_at) {
            return $hoursAgo > 0 ? [] : 0;
        }

        $rarityInfo = $this->valuablesHelper->getValuableRarityInfo($valuable->rarity);
        if (!$rarityInfo) {
            return $hoursAgo > 0 ? [] : 0;
        }

        $minPrice = $rarityInfo['base_value_min'];
        $maxPrice = $rarityInfo['base_value_max'];
        $stepSize = ($maxPrice - $minPrice) * 0.1;

        $currentHoursSinceTheft = now()->diffInHours($valuable->attempted_at);
        $prices = [];

        // Initialize random seed for deterministic price walk
        mt_srand($valuable->id);
        $price = ($minPrice + $maxPrice) / 2;

        // Walk through each hour from theft to present
        for ($hour = 0; $hour <= $currentHoursSinceTheft; $hour++) {
            // Apply random walk step
            if ($hour > 0) {
                // Single random value: -100 to +100 for both direction and volatility
                $randomStep = mt_rand(-100, 100) / 100;
                $price += $randomStep * $stepSize;
                $price = clamp($price, $minPrice, $maxPrice);
            }

            // Store price if it falls within our requested range
            $hoursBeforeNow = $currentHoursSinceTheft - $hour;
            if ($hoursAgo > 0 && $hoursBeforeNow <= $hoursAgo) {
                $prices[$hoursBeforeNow] = (int) round($price);
            }
        }

        mt_srand();

        // Return current price as int, or array of historical prices
        if ($hoursAgo === 0) {
            return (int) round($price);
        }

        return $prices;
    }

    /**
     * Get accumulated spy-hours for a valuable investigation
     * Capped at the required spy_hours for the valuable
     *
     * @param Valuable $valuable
     * @return int Total spy-hours accumulated (spies_assigned * hours_investigated)
     */
    public function getTheftProgress(Valuable $valuable): int
    {
        if (!$valuable->investigation_started_at || $valuable->spies_assigned === 0) {
            return 0;
        }

        $hoursInvestigated = now()->diffInHours($valuable->investigation_started_at);
        $progress = $valuable->spies_assigned * $hoursInvestigated;

        // Cap at required spy_hours
        if ($valuable->spy_hours !== null) {
            $progress = min($progress, $valuable->spy_hours);
        }

        return $progress;
    }
}
