<?php

namespace OpenDominion\Calculators\Dominion\Actions;

use OpenDominion\Calculators\Dominion\BuildingCalculator;
use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\ImprovementCalculator;
use OpenDominion\Models\Dominion;

class ConstructionCalculator
{
    /** @var BuildingCalculator */
    protected $buildingCalculator;

    /** @var LandCalculator */
    protected $landCalculator;

    /** @var ImprovementCalculator */
    protected $improvementCalculator;

    /**
     * ConstructionCalculator constructor.
     *
     * @param BuildingCalculator $buildingCalculator
     * @param LandCalculator $landCalculator
     */
    public function __construct(
        BuildingCalculator $buildingCalculator,
        LandCalculator $landCalculator,
        ImprovementCalculator $improvementCalculator)
    {
        $this->buildingCalculator = $buildingCalculator;
        $this->landCalculator = $landCalculator;
        $this->improvementCalculator = $improvementCalculator;
    }

    /**
     * Returns the Dominion's construction platinum cost (per building).
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getPlatinumCost(Dominion $dominion): int
    {
        return ($this->getPlatinumCostRaw($dominion) * $this->getPlatinumCostMultiplier($dominion));
    }

    /**
     * Returns the Dominion's raw construction platinum cost (per building).
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getPlatinumCostRaw(Dominion $dominion): int
    {
        $platinum = 0;
        $totalBuildings = $this->buildingCalculator->getTotalBuildings($dominion);
        $totalLand = $this->landCalculator->getTotalLand($dominion);

        $platinum += max(
            max($totalBuildings, 250),
            (3 * $totalLand) / 4
        );

        $platinum -= 250;
        $platinum *= 1.53;
        $platinum += 850;

        # ODA: Reduced by 25% as of Round 11.
        $platinum *= 0.75;

        return round($platinum);
    }

    /**
     * Returns the Dominion's construction platinum cost multiplier.
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getPlatinumCostMultiplier(Dominion $dominion): float
    {
        $multiplier = $this->getCostMultiplier($dominion);

        // Racial Bonus
        $multiplier += $dominion->race->getPerkMultiplier('construction_cost');
        // Techs
        $multiplier += $dominion->getTechPerkMultiplier('construction_cost');

        return $multiplier;
    }

    /**
     * Returns the Dominion's construction platinum cost for a given number of acres.
     *
     * @param Dominion $dominion
     * @param int $acres
     * @return int
     */
    public function getTotalPlatinumCost(Dominion $dominion, int $acres): int
    {
        $platinumCost = $this->getPlatinumCost($dominion);
        $totalPlatinumCost = $platinumCost * $acres;

        // Check for discounted acres after invasion
        $discountedAcres = min($dominion->discounted_land, $acres);
        if ($discountedAcres > 0) {
            #$totalPlatinumCost -= (int)ceil(($platinumCost * $discountedAcres) * 0.50);
            $totalPlatinumCost -= (int)ceil(($platinumCost * $discountedAcres) * 0.75);
        }

        return $totalPlatinumCost;
    }

    /**
     * Returns the Dominion's construction lumber cost (per building).
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getLumberCost(Dominion $dominion): int
    {
      if($dominion->race->getPerkMultiplier('no_lumber_construction_cost'))
      {
        return 0;
      }
      else
      {
        return ($this->getLumberCostRaw($dominion) * $this->getLumberCostMultiplier($dominion));
      }
    }

    /**
     * Returns the Dominion's raw construction lumber cost (per building).
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getLumberCostRaw(Dominion $dominion): int
    {
        $lumber = 0;
        $totalBuildings = $this->buildingCalculator->getTotalBuildings($dominion);
        $totalLand = $this->landCalculator->getTotalLand($dominion);

        $lumber += max(
            max($totalBuildings, 250),
            (3 * $totalLand) / 4
        );

        $lumber -= 250;
        $lumber *= 0.35;
        $lumber += 87.5;

        # ODA: Reduced by 25% as of Round 11.
        $lumber *= 0.75;

        return round($lumber);
    }

    /**
     * Returns the Dominion's construction lumber cost multiplier.
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getLumberCostMultiplier(Dominion $dominion): float
    {
        return $this->getCostMultiplier($dominion);
    }

    /**
     * Returns the Dominion's construction lumber cost for a given number of acres.
     *
     * @param Dominion $dominion
     * @param int $acres
     * @return int
     */
    public function getTotalLumberCost(Dominion $dominion, int $acres): int
    {
        $lumberCost = $this->getLumberCost($dominion);
        $totalLumberCost = $lumberCost * $acres;

        // Check for discounted acres after invasion
        $discountedAcres = min($dominion->discounted_land, $acres);
        if ($discountedAcres > 0) {
            #$totalLumberCost -= (int)ceil(($lumberCost * $discountedAcres) / 2);
            $totalLumberCost -= (int)ceil(($lumberCost * $discountedAcres) * 0.75);
        }

        return $totalLumberCost;
    }

    /**
     * Returns the maximum number of building a Dominion can construct.
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getMaxAfford(Dominion $dominion): int
    {
        $discountedBuildings = 0;
        $platinumToSpend = $dominion->resource_platinum;
        $lumberToSpend = $dominion->resource_lumber;
        $barrenLand = $this->landCalculator->getTotalBarrenLand($dominion);
        $platinumCost = $this->getPlatinumCost($dominion);
        $lumberCost = $this->getLumberCost($dominion);

        // Check for discounted acres after invasion
        if ($dominion->discounted_land > 0) {
            $maxFromDiscountedPlatinum = (int)floor($platinumToSpend / ($platinumCost / 2));
            $maxFromDiscountedLumber = (int)floor($lumberToSpend / ($lumberCost / 2));
            // Set the number of afforded discounted buildings
            $discountedBuildings = min(
                $maxFromDiscountedPlatinum,
                $maxFromDiscountedLumber,
                $dominion->discounted_land,
                $barrenLand
            );
            // Subtract discounted building cost from available resources
            $platinumToSpend -= (int)ceil(($platinumCost * $discountedBuildings) / 2);
            $lumberToSpend -= (int)ceil(($lumberCost * $discountedBuildings) / 2);
        }

        # Merfolk perk: no_lumber_construction_cost
        if($dominion->race->getPerkMultiplier('no_lumber_construction_cost'))
        {
          return $discountedBuildings + min(
                  floor($platinumToSpend / $platinumCost),
                  ($barrenLand - $discountedBuildings)
              );
        }
        else
        {
          return $discountedBuildings + min(
                  floor($platinumToSpend / $platinumCost),
                  floor($lumberToSpend / $lumberCost),
                  ($barrenLand - $discountedBuildings)
              );
        }



    }

    /**
     * Returns the Dominion's global construction cost multiplier.
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getCostMultiplier(Dominion $dominion): float
    {
        $multiplier = 0;

        $maxReduction = -90;

        // Values (percentages)
        $factoryReduction = 4;
        $factoryReductionMax = 75;

        // Factories
        $multiplier -= min(
            (($dominion->building_factory / $this->landCalculator->getTotalLand($dominion)) * $factoryReduction),
            ($factoryReductionMax / 100)
        );

        # Workshops
        $multiplier -= $this->improvementCalculator->getImprovementMultiplierBonus($dominion, 'workshops');

        $multiplier = max($multiplier, $maxReduction);

        return (1 + $multiplier);
    }
}
