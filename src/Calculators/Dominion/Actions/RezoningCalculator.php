<?php

namespace OpenDominion\Calculators\Dominion\Actions;

use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\SpellCalculator;
use OpenDominion\Calculators\Dominion\ImprovementCalculator;
use OpenDominion\Models\Dominion;

class RezoningCalculator
{
    /** @var LandCalculator */
    protected $landCalculator;

    /** @var SpellCalculator */
    protected $spellCalculator;

    /** @var ImprovementCalculator */
    protected $improvementCalculator;

    /**
     * RezoningCalculator constructor.
     *
     * @param LandCalculator $landCalculator
     */
    public function __construct(
        LandCalculator $landCalculator,
        SpellCalculator $spellCalculator,
        ImprovementCalculator $improvementCalculator
    ) {
        $this->landCalculator = $landCalculator;
        $this->spellCalculator = $spellCalculator;
        $this->improvementCalculator = $improvementCalculator;
    }

    /**
     * Returns the Dominion's rezoning platinum cost (per acre of land).
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getPlatinumCost(Dominion $dominion): int
    {

        if($dominion->race->getPerkMultiplier('construction_cost_only_mana') or $dominion->race->getPerkMultiplier('construction_cost_only_food'))
        {
          return 0;
        }
        else
        {
          $platinum = 0;

          $platinum += $this->landCalculator->getTotalLand($dominion);

          $platinum -= 250;
          $platinum *= 0.6;
          $platinum += 250;

          $platinum *= $this->getCostMultiplier($dominion);

          return round($platinum);
        }

    }


    /**
     * Returns the Dominion's rezoning food cost (per acre of land).
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getFoodCost(Dominion $dominion): int
    {
      if($dominion->race->getPerkMultiplier('construction_cost_only_food'))
      {
        $food = 0;

        $food += $this->landCalculator->getTotalLand($dominion);

        $food -= 250;
        $food *= 0.6;
        $food += 250;

        $food *= $this->getCostMultiplier($dominion);

        return round($food);
      }
      else
      {
        return 0;
      }
    }


    /**
     * Returns the Dominion's rezoning mana cost (per acre of land).
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getManaCost(Dominion $dominion): int
    {
        if($dominion->race->getPerkMultiplier('construction_cost_only_mana'))
        {
          $mana = 0;

          $mana += $this->landCalculator->getTotalLand($dominion);

          $mana -= 250;
          $mana *= 0.6;
          $mana += 250;

          $mana *= $this->getCostMultiplier($dominion);

          return round($mana);
        }
        else
        {
          return 0;
        }
    }

    /**
     * Returns the maximum number of acres of land a Dominion can rezone.
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getMaxAfford(Dominion $dominion): int
    {
      $platinumCost = $this->getPlatinumCost($dominion);
      $foodCost = $this->getFoodCost($dominion);
      $manaCost = $this->getManaCost($dominion);

      if($foodCost > 0)
      {
        $maxAfford = min(
          floor($dominion->resource_food / $foodCost),
          $this->landCalculator->getTotalBarrenLand($dominion)
        );

      }
      if($manaCost > 0)
      {
        $maxAfford = min(
          floor($dominion->resource_mana / $manaCost),
          $this->landCalculator->getTotalBarrenLand($dominion)
        );

      }
      else
      {
        $maxAfford = min(
          floor($dominion->resource_platinum / $platinumCost),
          $this->landCalculator->getTotalBarrenLand($dominion)
        );
      }

      return $maxAfford;

    }

    /**
     * Returns the Dominion's rezoning cost multiplier.
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getCostMultiplier(Dominion $dominion): float
    {
        $multiplier = 0;

        $maxReduction = -90;

        // Values (percentages)
        $factoryReduction = 3;
        $factoryReductionMax = 75;

        // Factories
        $multiplier -= min(
            (($dominion->building_factory / $this->landCalculator->getTotalLand($dominion)) * $factoryReduction),
            ($factoryReductionMax / 100)
        );

        # Workshops
        $multiplier -= $this->improvementCalculator->getImprovementMultiplierBonus($dominion, 'workshops');

        // Techs
        $multiplier += $dominion->getTechPerkMultiplier('rezone_cost');

        $multiplier = max($multiplier, $maxReduction);

        return (1 + $multiplier);
    }
}
