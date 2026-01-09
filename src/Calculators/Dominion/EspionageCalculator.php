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
     * The price walks randomly for up to 48 hours (EXPIRATION_HOURS), then remains constant.
     * When $hoursAgo is 0, returns the current price as an integer.
     * When $hoursAgo > 0, returns an array of prices from $hoursAgo down to 0.
     * If the valuable was stolen more recently than $hoursAgo, earlier hours are padded
     * with the starting price (midpoint between min and max).
     *
     * @param Valuable $valuable
     * @param int $hoursAgo Number of hours of price history to return (0 = current price only)
     * @return int|array Returns int when $hoursAgo=0, array otherwise
     */
    public function getValuableSellPrice(Valuable $valuable, int $hoursAgo = 0)
    {
        if (!$valuable->completed_at) {
            return $hoursAgo > 0 ? [] : 0;
        }

        $rarityInfo = $this->valuablesHelper->getValuableRarityInfo($valuable->rarity);
        if (!$rarityInfo) {
            return $hoursAgo > 0 ? [] : 0;
        }

        $minPrice = $rarityInfo['base_value_min'];
        $maxPrice = $rarityInfo['base_value_max'];
        $stepSize = ($maxPrice - $minPrice) * $this->valuablesHelper::PRICE_VOLATILITY;

        $currentHoursSinceTheft = now()->diffInHours($valuable->completed_at);
        $priceWalkLimit = $this->valuablesHelper::EXPIRATION_HOURS; // 48 hours
        $prices = [];

        // Initialize random seed for deterministic price walk
        mt_srand($valuable->id);
        $price = ($minPrice + $maxPrice) / 2;
        $priceAtLimit = null;

        // Walk through each hour from theft to price walk limit or present (whichever is earlier)
        $walkUntil = min($currentHoursSinceTheft, $priceWalkLimit);
        for ($hour = 0; $hour <= $walkUntil; $hour++) {
            // Apply random walk step
            if ($hour > 0) {
                // Single random value: -100 to +100 for both direction and volatility
                $randomStep = mt_rand(-100, 100) / 100;
                $price += $randomStep * $stepSize;
                $price = clamp($price, $minPrice, $maxPrice);
            }

            // Store the price at the limit for use beyond that point
            if ($hour === $priceWalkLimit) {
                $priceAtLimit = (int) round($price);
            }

            // Store price if it falls within our requested range
            $hoursBeforeNow = $currentHoursSinceTheft - $hour;
            if ($hoursAgo > 0 && $hoursBeforeNow <= $hoursAgo) {
                $prices[$hoursBeforeNow] = (int) round($price);
            }
        }

        // If we're past the price walk limit, fill remaining hours with the price at limit
        if ($currentHoursSinceTheft > $priceWalkLimit) {
            $finalPrice = $priceAtLimit ?? (int) round($price);
            for ($hour = $priceWalkLimit + 1; $hour <= $currentHoursSinceTheft; $hour++) {
                $hoursBeforeNow = $currentHoursSinceTheft - $hour;
                if ($hoursAgo > 0 && $hoursBeforeNow <= $hoursAgo) {
                    $prices[$hoursBeforeNow] = $finalPrice;
                }
            }
            // Update current price to the final price
            $price = $finalPrice;
        }

        mt_srand();

        // Return current price as int, or array of historical prices
        if ($hoursAgo === 0) {
            return (int) round($price);
        }

        // If we don't have enough price history, pad the beginning with starting price
        if ($currentHoursSinceTheft < $hoursAgo) {
            $startingPrice = (int) round(($minPrice + $maxPrice) / 2);
            for ($hour = $currentHoursSinceTheft + 1; $hour <= $hoursAgo; $hour++) {
                $prices[$hour] = $startingPrice;
            }
        }

        // Sort by hour (descending: most recent first)
        krsort($prices);

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
