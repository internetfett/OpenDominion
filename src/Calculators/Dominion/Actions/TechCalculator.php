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
     * Returns the Dominion's current research point cost to unlock a new tech.
     *
     * @param Dominion $dominion
     * @return int
     */
    public function getTechCost(Dominion $dominion): int
    {
        $techCostMultiplier = 5;
        $techCostBonusMultiplier = 1;
        $minimumCost = intval(1000 * $techCostMultiplier);

        # Perk
        if($dominion->race->getPerkMultiplier('tech_costs'))
        {
          $techCostBonusMultiplier += $dominion->race->getPerkMultiplier('tech_costs');
        }

        # Observatory
        $techCostBonusMultiplier -= $this->improvementCalculator->getImprovementMultiplierBonus($dominion, 'observatory');

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
