<?php

namespace OpenDominion\Services\Dominion\Actions;

use OpenDominion\Exceptions\GameException;
use OpenDominion\Models\Dominion;
use OpenDominion\Services\Dominion\HistoryService;
use OpenDominion\Traits\DominionGuardsTrait;

class DailyBonusesActionService
{
    use DominionGuardsTrait;

    /**
     * Claims the daily platinum bonus for a Dominion.
     *
     * @param Dominion $dominion
     * @return array
     * @throws GameException
     */
    public function claimPlatinum(Dominion $dominion): array
    {
        if ($dominion->daily_platinum) {
            throw new GameException('You already claimed your platinum bonus for today.');
        }

        if($dominion->race->name == 'Growth')
        {
          $resourceType = 'resource_food';
          $amountModifier = 1;
        }
        elseif($dominion->race->name == 'Gnome' or $dominion->race->name == 'Imperial Gnome')
        {
          $resourceType = 'resource_ore';
          $amountModifier = 1;
        }
        elseif($dominion->race->name == 'Void')
        {
          $resourceType = 'resource_mana';
          $amountModifier = 1;
        }
        else
        {
          $resourceType = 'resource_platinum';
          $amountModifier = 1;
        }

        $bonusAmount = $dominion->peasants * 4 * $amountModifier;
        $dominion->increment($resourceType, $bonusAmount);
        $dominion->daily_platinum = true;
        $dominion->save(['event' => HistoryService::EVENT_ACTION_DAILY_BONUS]);

        return [
            'message' => sprintf(
                'You gain %s ' . str_replace('resource_', '', $resourceType) . '.',
                number_format($bonusAmount)
            ),
            'data' => [
                'bonusAmount' => $bonusAmount,
            ],
        ];
    }

    /**
     * Claims the daily land bonus for a Dominion.
     *
     * @param Dominion $dominion
     * @return array
     * @throws GameException
     */
    public function claimLand(Dominion $dominion): array
    {
        if ($dominion->daily_land) {
            throw new GameException('You already claimed your land bonus for today.');
        }

#        $landGained = 20;
        $landGained = rand(1,200) == 1 ? 100 : rand(10, 40);
        $attribute = ('land_' . $dominion->race->home_land_type);
        $dominion->increment($attribute, $landGained);
        $dominion->daily_land = true;
        $dominion->save(['event' => HistoryService::EVENT_ACTION_DAILY_BONUS]);

        return [
            'message' => sprintf(
                'You gain %d acres of %s.',
                $landGained,
                str_plural($dominion->race->home_land_type)
            ),
            'data' => [
                'landGained' => $landGained,
            ],
        ];
    }
}
