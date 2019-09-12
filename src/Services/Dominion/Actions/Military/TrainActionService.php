<?php

namespace OpenDominion\Services\Dominion\Actions\Military;

use DB;
use OpenDominion\Calculators\Dominion\Actions\TrainingCalculator;
use OpenDominion\Exceptions\GameException;
use OpenDominion\Helpers\UnitHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Services\Dominion\HistoryService;
use OpenDominion\Services\Dominion\QueueService;
use OpenDominion\Traits\DominionGuardsTrait;
use Throwable;

class TrainActionService
{
    use DominionGuardsTrait;

    /** @var QueueService */
    protected $queueService;

    /** @var TrainingCalculator */
    protected $trainingCalculator;

    /** @var UnitHelper */
    protected $unitHelper;

    /**
     * TrainActionService constructor.
     */
    public function __construct()
    {
        $this->queueService = app(QueueService::class);
        $this->trainingCalculator = app(TrainingCalculator::class);
        $this->unitHelper = app(UnitHelper::class);
    }

    /**
     * Does a military train action for a Dominion.
     *
     * @param Dominion $dominion
     * @param array $data
     * @return array
     * @throws Throwable
     */
    public function train(Dominion $dominion, array $data): array
    {
        $this->guardLockedDominion($dominion);

        $data = array_only($data, array_map(function ($value) {
            return "military_{$value}";
        }, $this->unitHelper->getUnitTypes()));

        $data = array_map('\intval', $data);

        $totalUnitsToTrain = array_sum($data);

        if ($totalUnitsToTrain === 0) {
            throw new GameException('Training aborted due to bad input.');
        }

        $totalCosts = [
            'platinum' => 0,
            'ore' => 0,
            'draftees' => 0,
            'wizards' => 0,

            //New unit cost resources
            'food' => 0,
            'mana' => 0,
            'gem' => 0,
            'lumber' => 0,
            'prestige' => 0,
            'boat' => 0,

        ];

        $unitsToTrain = [];

        $trainingCostsPerUnit = $this->trainingCalculator->getTrainingCostsPerUnit($dominion);

        foreach ($data as $unitType => $amountToTrain) {
            if (!$amountToTrain) {
                continue;
            }

            $unitType = str_replace('military_', '', $unitType);

            $costs = $trainingCostsPerUnit[$unitType];

            foreach ($costs as $costType => $costAmount) {
                $totalCosts[$costType] += ($amountToTrain * $costAmount);
            }

            $unitsToTrain[$unitType] = $amountToTrain;
        }


        if($totalCosts['platinum'] > $dominion->resource_platinum)
        {
          throw new GameException('Training failed due to insufficient platinum.');
        }
        if($totalCosts['ore'] > $dominion->resource_ore)
        {
          throw new GameException('Training failed due to insufficient ore.');
        }
        if($totalCosts['food'] > $dominion->resource_food)
        {
          throw new GameException('Training failed due to insufficient food.');
        }
        if($totalCosts['mana'] > $dominion->resource_mana)
        {
          throw new GameException('Training failed due to insufficient mana.');
        }
        if($totalCosts['gem'] > $dominion->resource_gems)
        {
          throw new GameException('Training failed due to insufficient gems.');
        }
        if($totalCosts['lumber'] > $dominion->resource_lumber)
        {
          throw new GameException('Training failed due to insufficient lumber.');
        }
        if($totalCosts['prestige'] > $dominion->prestige)
        {
          throw new GameException('Training failed due to insufficient prestige.');
        }
        if($totalCosts['boat'] > $dominion->resource_boats)
        {
          throw new GameException('Training failed due to insufficient boats.');
        }

        if(isset($unitsToTrain['unit3']) or isset($unitsToTrain['unit4']))
        {
          // If training elites, check if ARMADA or IMPERIAL GNOME to calculate unit housing (Docks / Factories)
          // ARMADA: Max 2 Boats per Dock (+ Harbour)
          if ($dominion->race->name == 'Armada' and (($dominion->military_unit3 + $dominion->military_unit4) + ($unitsToTrain['unit3'] + $unitsToTrain['unit4'])) > ($dominion->building->docks * 2))
          {
            throw new GameException('You cannot control that many ships. Max 2 ships per Dock. Increased by Harbor.');
          }
          // IMPERIAL GNOME: Max 2 Machines per Factory (+ Science)
          if ($dominion->race->name == 'Imperial Gnome' and (CURRENT_AMOUNT_OF_SHIPS + NEW_SHIPS) > (FACTORIES * (SCIENCE * 2) * 2))
          {
            throw new GameException('You cannot control that many machines. Max 2 machines per Factory. Increased by Science.');
          }
        }


        if ($totalCosts['draftees'] > $dominion->military_draftees) {
            throw new GameException('Training aborted due to lack of draftees');
        }

        if ($totalCosts['wizards'] > $dominion->military_wizards) {
            throw new GameException('Training aborted due to lack of wizards');
        }

        DB::transaction(function () use ($dominion, $data, $totalCosts) {
            $dominion->decrement('resource_platinum', $totalCosts['platinum']);
            $dominion->decrement('resource_ore', $totalCosts['ore']);
            $dominion->decrement('military_draftees', $totalCosts['draftees']);
            $dominion->decrement('military_wizards', $totalCosts['wizards']);

            // New unit cost resources.

            $dominion->decrement('resource_food', $totalCosts['food']);
            $dominion->decrement('resource_mana', $totalCosts['mana']);
            $dominion->decrement('resource_gems', $totalCosts['gem']);
            $dominion->decrement('resource_lumber', $totalCosts['lumber']);
            $dominion->decrement('prestige', $totalCosts['prestige']);
            $dominion->decrement('resource_boats', $totalCosts['boat']);

            $dominion->save(['event' => HistoryService::EVENT_ACTION_TRAIN]);

            // Specialists train in 9 hours
            $nineHourData = [
                'military_unit1' => $data['military_unit1'],
                'military_unit2' => $data['military_unit2'],
            ];
            unset($data['military_unit1'], $data['military_unit2']);

            $this->queueService->queueResources('training', $dominion, $nineHourData, 9);
            $this->queueService->queueResources('training', $dominion, $data);
        });

        return [
            'message' => $this->getReturnMessageString($dominion, $unitsToTrain, $totalCosts),
            'data' => [
                'totalCosts' => $totalCosts,
            ],
        ];
    }

    /**
     * Returns the message for a train action.
     *
     * @param Dominion $dominion
     * @param array $unitsToTrain
     * @param array $totalCosts
     * @return string
     */
    protected function getReturnMessageString(Dominion $dominion, array $unitsToTrain, array $totalCosts): string
    {
        $unitsToTrainStringParts = [];

        foreach ($unitsToTrain as $unitType => $amount) {
            if ($amount > 0) {
                $unitName = strtolower($this->unitHelper->getUnitName($unitType, $dominion->race));

                // str_plural() isn't perfect for certain unit names. This array
                // serves as an override to use (see issue #607)
                // todo: Might move this to UnitHelper, especially if more
                //       locations need unit name overrides
                $overridePluralUnitNames = [
                    'shaman' => 'shamans',
                    'abscess' => 'abscesses',
                    'werewolf' => 'werewolves',
                    'snow witch' => 'snow witches',
                    'lich' => 'liches',
                    'progeny' => 'progenies',
                    'fallen' => 'fallen',
                    'goat witch' => 'goat witches',
                    'phoenix' => 'phoenix',
                    'master thief' => 'master thieves',
                    'cavalry' => 'cavalries',
                    'pikeman' => 'pikemen'
                ];

                $amountLabel = number_format($amount);

                if (array_key_exists($unitName, $overridePluralUnitNames)) {
                    if ($amount === 1) {
                        $unitLabel = $unitName;
                    } else {
                        $unitLabel = $overridePluralUnitNames[$unitName];
                    }
                } else {
                    $unitLabel = str_plural(str_singular($unitName), $amount);
                }

                $unitsToTrainStringParts[] = "{$amountLabel} {$unitLabel}";
            }
        }

        $unitsToTrainString = generate_sentence_from_array($unitsToTrainStringParts);

        $trainingCostsStringParts = [];
        foreach ($totalCosts as $costType => $cost) {
            if ($cost === 0) {
                continue;
            }

            $costType = str_singular($costType);
#            if (!\in_array($costType, ['platinum', 'ore'], true)) {
            if (!\in_array($costType, ['platinum', 'ore', 'food', 'mana', 'gem', 'lumber', 'prestige', 'boat'], true)) {

                $costType = str_plural($costType, $cost);
            }
            $trainingCostsStringParts[] = (number_format($cost) . ' ' . $costType);

        }

        $trainingCostsString = generate_sentence_from_array($trainingCostsStringParts);

        $message = sprintf(
            'Training of %s begun at a cost of %s.',
            $unitsToTrainString,
            $trainingCostsString
        );

        return $message;
    }
}
