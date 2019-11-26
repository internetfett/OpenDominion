<?php

namespace OpenDominion\Calculators\Dominion\Actions;

use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\ImprovementCalculator;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Tech;

class TechCalculator
{
    /** @var LandCalculator */
    protected $landCalculator;

    /** @var ImprovementCalculator */
    protected $improvementCalculator;

    /**
     * TechCalculator constructor.
     *
     * @param LandCalculator $landCalculator
     */
    public function __construct(
        LandCalculator $landCalculator,
        ImprovementCalculator $improvementCalculator)
    {
        $this->landCalculator = $landCalculator;
        $this->improvementCalculator = $improvementCalculator;
    }

    /**
     * Returns the Dominion's current experience point cost to unlock a new tech.
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getTechCost(Dominion $dominion, Tech $techToUnlock): int
    {

        $techToUnlock = Tech::where('key', $techToUnlock->key)->first();

        $techCostMultiplier = 5;
        $techCostMultiplier *= (1 + $techToUnlock->cost_multiplier / 100);

        $minimumCost = 10000;

        $techCostBonusMultiplier = 1;

        # Perk
        if($dominion->race->getPerkMultiplier('tech_costs'))
        {
          $techCostBonusMultiplier += $dominion->race->getPerkMultiplier('tech_costs');
        }

        # Minimum also affected by tech cost multiplier.
        $minimumCost *= $techCostMultiplier;

        return max($minimumCost, ($techCostMultiplier * $this->landCalculator->getTotalLand($dominion) * $techCostBonusMultiplier));

    }

    /**
     * Determine if the Dominion meets the requirements to unlock a new tech.
     *
     * @param Dominion $dominion
     * @return bool
     */
    public function hasPrerequisites(Dominion $dominion, Tech $tech): bool
    {
        $unlockedTechs = $dominion->techs->pluck('key')->all();

        return count(array_diff($tech->prerequisites, $unlockedTechs)) == 0;
    }
}
