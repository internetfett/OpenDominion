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
      if($dominion->race->getPerkMultiplier('construction_cost_only_platinum'))
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

### MANA VOID

        /**
         * Returns the Dominion's construction mana cost (per building).
         *
         * @param Dominion $dominion
         * @return float
         */
        public function getManaCost(Dominion $dominion): int
        {
            return ($this->getManaCostRaw($dominion) * $this->getManaCostMultiplier($dominion));
        }

        /**
         * Returns the Dominion's raw construction mana cost (per building).
         *
         * @param Dominion $dominion
         * @return int
         */
        public function getManaCostRaw(Dominion $dominion): int
        {
            $mana = 0;
            $totalBuildings = $this->buildingCalculator->getTotalBuildings($dominion);
            $totalLand = $this->landCalculator->getTotalLand($dominion);

            $mana += max(
                max($totalBuildings, 250),
                (3 * $totalLand) / 4
            );

            $mana -= 250;
            $mana *= 1.53;
            $mana += 850;

            # ODA: Reduced by 25% as of Round 11.
            $mana *= 0.75;

            return round($mana);
        }

        /**
         * Returns the Dominion's construction mana cost multiplier.
         *
         * @param Dominion $dominion
         * @return float
         */
        public function getManaCostMultiplier(Dominion $dominion): float
        {
            $multiplier = $this->getCostMultiplier($dominion);

            return $multiplier;
        }

        /**
         * Returns the Dominion's construction mana cost for a given number of acres.
         *
         * @param Dominion $dominion
         * @param int $acres
         * @return int
         */
        public function getTotalManaCost(Dominion $dominion, int $acres): int
        {
            $manaCost = $this->getManaCost($dominion);
            $totalManaCost = $manaCost * $acres;

            // Check for discounted acres after invasion
            $discountedAcres = min($dominion->discounted_land, $acres);
            if ($discountedAcres > 0) {
                #$totalPlatinumCost -= (int)ceil(($platinumCost * $discountedAcres) * 0.50);
                $totalManaCost -= (int)ceil(($platinumCost * $discountedAcres) * 0.75);
            }

            return $totalManaCost;
        }

### MANA VOID


### FOOD GROWTH MYCONID

        /**
         * Returns the Dominion's construction food cost (per building).
         *
         * @param Dominion $dominion
         * @return float
         */
        public function getFoodCost(Dominion $dominion): int
        {
            return ($this->getFoodCostRaw($dominion) * $this->getFoodCostMultiplier($dominion));
        }

        /**
         * Returns the Dominion's raw construction mana cost (per building).
         *
         * @param Dominion $dominion
         * @return int
         */
        public function getFoodCostRaw(Dominion $dominion): int
        {
            $food = 0;
            $totalBuildings = $this->buildingCalculator->getTotalBuildings($dominion);
            $totalLand = $this->landCalculator->getTotalLand($dominion);

            $food += max(
                max($totalBuildings, 250),
                (3 * $totalLand) / 4
            );

            $food -= 250;
            $food *= 1.53;
            $food += 850;

            # ODA: Reduced by 25% as of Round 11.
            $food *= 0.75;

            return round($mana);
        }

        /**
         * Returns the Dominion's construction mana cost multiplier.
         *
         * @param Dominion $dominion
         * @return float
         */
        public function getFoodCostMultiplier(Dominion $dominion): float
        {
            $multiplier = $this->getCostMultiplier($dominion);

            return $multiplier;
        }

        /**
         * Returns the Dominion's construction mana cost for a given number of acres.
         *
         * @param Dominion $dominion
         * @param int $acres
         * @return int
         */
        public function getTotalFoodCost(Dominion $dominion, int $acres): int
        {
            $foodCost = $this->getFoodCost($dominion);
            $totalFoodCost = $foodCost * $acres;

            // Check for discounted acres after invasion
            $discountedAcres = min($dominion->discounted_land, $acres);
            if ($discountedAcres > 0) {
                #$totalPlatinumCost -= (int)ceil(($platinumCost * $discountedAcres) * 0.50);
                $totalFoodCost -= (int)ceil(($platinumCost * $discountedAcres) * 0.75);
            }

            return $totalFoodCost;
        }

### FOOD GROWTH MYCONID

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
        $manaToSpend = $dominion->resource_mana;
        $foodToSpend = $dominion->resource_food;
        $barrenLand = $this->landCalculator->getTotalBarrenLand($dominion);
        $platinumCost = $this->getPlatinumCost($dominion);
        $lumberCost = $this->getLumberCost($dominion);
        $manaCost = $this->getManaCost($dominion);
        $foodCost = $this->getFoodCost($dominion);

        // Check for discounted acres after invasion
        if ($dominion->discounted_land > 0)
        {
            $maxFromDiscountedPlatinum = (int)floor($platinumToSpend / ($platinumCost / 2));
            $maxFromDiscountedLumber = (int)floor($lumberToSpend / ($lumberCost / 2));
            $maxFromDiscountedMana = (int)floor($manaToSpend / ($manaCost / 2));
            $maxFromDiscountedFood = (int)floor($foodToSpend / ($foodCost / 2));
            // Set the number of afforded discounted buildings
            $discountedBuildings = min(
                $maxFromDiscountedPlatinum,
                $maxFromDiscountedLumber,
                $maxFromDiscountedMana,
                $maxFromDiscountedFood,
                $dominion->discounted_land,
                $barrenLand
            );
            // Subtract discounted building cost from available resources
            $platinumToSpend -= (int)ceil(($platinumCost * $discountedBuildings) / 2);
            $lumberToSpend -= (int)ceil(($lumberCost * $discountedBuildings) / 2);
            $manaToSpend -= (int)ceil(($manaCost * $discountedBuildings) / 2);
            $foodToSpend -= (int)ceil(($foodCost * $discountedBuildings) / 2);
        }

        # Merfolk perk: construction_cost_only_platinum
        if($dominion->race->getPerkMultiplier('construction_cost_only_platinum'))
        {
          return $discountedBuildings + min(
                  floor($platinumToSpend / $platinumCost),
                  ($barrenLand - $discountedBuildings)
              );
        }
        elseif($dominion->race->getPerkMultiplier('construction_cost_only_mana'))
        {
          return $discountedBuildings + min(
                  floor($manaToSpend / $manaCost),
                  ($barrenLand - $discountedBuildings)
              );
        }
        elseif($dominion->race->getPerkMultiplier('construction_cost_only_food'))
        {
          return $discountedBuildings + min(
                  floor($foodToSpend / $foodCost),
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
