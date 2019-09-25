<?php

namespace OpenDominion\Calculators\Dominion;

use OpenDominion\Models\Dominion;

class PrestigeCalculator
{
    /**
     * Returns the Dominion's prestige multiplier.
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getPrestigeMultiplier(Dominion $dominion): float
    {
        return ($dominion->prestige / 10000);
    }

    /**
     * Returns the multiplier to be applied to Beastfolk land based bonuses.
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getBeastfolkPrestigeLandBonusMultiplier(Dominion $dominion): float
    {
        if($dominion->race->name !== 'Beastfolk')
        {
          return 1;
        }
        else
        {
          1 + (($dominion->prestige / 50) / 100);
        }

        return ($dominion->prestige / 10000);
    }

}
