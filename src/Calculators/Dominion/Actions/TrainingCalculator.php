<?php

namespace OpenDominion\Calculators\Dominion\Actions;

use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Helpers\UnitHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Calculators\Dominion\ImprovementCalculator;

class TrainingCalculator
{
    /** @var LandCalculator */
    protected $landCalculator;

    /** @var UnitHelper */
    protected $unitHelper;

    /** @var ImprovementCalculator */
    protected $improvementCalculator;

    /**
     * TrainingCalculator constructor.
     *
     * @param LandCalculator $landCalculator
     * @param UnitHelper $unitHelper
     */
    public function __construct(LandCalculator $landCalculator, UnitHelper $unitHelper, ImprovementCalculator $improvementCalculator)
    {
        $this->landCalculator = $landCalculator;
        $this->unitHelper = $unitHelper;
        $this->improvementCalculator = $improvementCalculator;
    }

    /**
     * Returns the Dominion's training costs per unit.
     *
     * @param Dominion $dominion
     * @return array
     */
    public function getTrainingCostsPerUnit(Dominion $dominion): array
    {
        $costsPerUnit = [];
        $archmageBaseCost = 1000;
        $archmageBaseCost += $dominion->race->getPerkValue('archmage_cost');

        $wizardCostMultiplier = $this->getWizardCostMultiplier($dominion);

        // Values
        $spyPlatinumCost = 500;
        $wizardPlatinumCost = (int)ceil(500 * $wizardCostMultiplier);
        $archmagePlatinumCost = (int)ceil($archmageBaseCost * $wizardCostMultiplier);

        $units = $dominion->race->units;

        foreach ($this->unitHelper->getUnitTypes() as $unitType) {
            $cost = [];

            switch ($unitType) {
                case 'spies':
                    $cost['draftees'] = 1;
                    $cost['platinum'] = $spyPlatinumCost;
                    break;

                case 'wizards':
                    $cost['draftees'] = 1;
                    $cost['platinum'] = $wizardPlatinumCost;
                    break;

                case 'archmages':
                    $cost['platinum'] = $archmagePlatinumCost;
                    $cost['wizards'] = 1;
                    break;

                default:
                    $unitSlot = (((int)str_replace('unit', '', $unitType)) - 1);

                    $platinum = $units[$unitSlot]->cost_platinum;
                    $ore = $units[$unitSlot]->cost_ore;

                    // New unit cost resources
                    $food = $units[$unitSlot]->cost_food;
                    $mana = $units[$unitSlot]->cost_mana;
                    $gem = $units[$unitSlot]->cost_gem;
                    $lumber = $units[$unitSlot]->cost_lumber;
                    $prestige = $units[$unitSlot]->cost_prestige;
                    $boat = $units[$unitSlot]->cost_boat;

                    if ($platinum > 0) {
                        $cost['platinum'] = (int)ceil($platinum * $this->getSpecialistEliteCostMultiplier($dominion, 'platinum'));
                    }

                    if ($ore > 0) {
                        $cost['ore'] = $ore;
                        $cost['ore'] = (int)ceil($ore * $this->getSpecialistEliteCostMultiplier($dominion, 'ore'));
                    }

                    // FOOD cost for units - Not affected by Smithies
                    if ($food > 0) {
                        $cost['food'] = $food;
                    }
                    // MANA cost for units - Not affected by Smithies
                    if ($mana > 0) {
                        $cost['mana'] = $mana;
                    }
                    // GEM cost for units - Not affected by Smithies
                    if ($gem > 0) {
                        $cost['gem'] = $gem;
                    }
                    // LUMBER cost for units - Not affected by Smithies
                    if ($lumber > 0) {
                        $cost['lumber'] = $lumber;
                    }
                    // PRESTIGE cost for units - Not affected by Smithies
                    if ($prestige > 0) {
                        $cost['prestige'] = $prestige;
                    }
                    // BOAT cost for units - Not affected by Smithies
                    if ($boat > 0) {
                        $cost['boat'] = $boat;
                    }

                    $cost['draftees'] = 1;

                    break;
            }

            $costsPerUnit[$unitType] = $cost;
        }

        return $costsPerUnit;
    }

    /**
     * Returns the Dominion's max military trainable population.
     *
     * @param Dominion $dominion
     * @return array
     */
    public function getMaxTrainable(Dominion $dominion): array
    {
        $trainable = [];

        $fieldMapping = [
            'platinum' => 'resource_platinum',
            'ore' => 'resource_ore',
            'draftees' => 'military_draftees',
            'wizards' => 'military_wizards',

            //New unit cost resources

            'food' => 'resource_food',
            'mana' => 'resource_mana',
            'gem' => 'resource_gems',
            'lumber' => 'resource_lumber',
            'prestige' => 'prestige',
            'boat' => 'resource_boats',
        ];

        $costsPerUnit = $this->getTrainingCostsPerUnit($dominion);

        foreach ($costsPerUnit as $unitType => $costs) {
            $trainableByCost = [];

            foreach ($costs as $type => $value) {

                /* Pray we never need this again.
                if($value == Null)
                {
                  echo '<pre>';
                  echo "\n".'$value is NULL';
                  echo "\n".'$costs is' . var_dump($costs);
                  echo "\n".'$value is ' . var_dump($value);
                  echo '</pre>';
                  $value = 1;
                }
                */
                $trainableByCost[$type] = (int)floor($dominion->{$fieldMapping[$type]} / $value);
            }

            $trainable[$unitType] = min($trainableByCost);
        }

        return $trainable;
    }

    /**
     * Returns the Dominion's training cost multiplier.
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getSpecialistEliteCostMultiplier(Dominion $dominion, string $resourceType): float
    {
        $multiplier = 0;

        // Values (percentages)
        $smithiesReduction = 2;
        $smithiesReductionMax = 36;

        // Smithies
        $exemptRaces = array('Gnome', 'Imperial Gnome');

        $multiplier -= min(
            (($dominion->building_smithy / $this->landCalculator->getTotalLand($dominion)) * $smithiesReduction),
            ($smithiesReductionMax / 100)
        );

        // Start multiplier back to zero if Ore and a Gnomish race.
        if($resourceType == 'ore' and !in_array($dominion->race->name, $exemptRaces))
        {
          $multiplier = 0;
        }

        // Armory
        if($this->improvementCalculator->getImprovementMultiplierBonus($dominion, 'armory') > 0)
        {
            $multiplier -= $this->improvementCalculator->getImprovementMultiplierBonus($dominion, 'armory');
        }

        // todo: Master of Resources Tech

        // Cap $multiplier at -50%
        $multiplier = max($multiplier, -50);

        return (1 + $multiplier);
    }

    /**
     * Returns the Dominion's training platinum cost multiplier for wizards and archmages.
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getWizardCostMultiplier(Dominion $dominion): float
    {
        $multiplier = 0;

        // Values (percentages)
        $wizardGuildReduction = 2;
        $wizardGuildReductionMax = 40;

        // Wizard Guilds
        $multiplier -= min(
            (($dominion->building_wizard_guild / $this->landCalculator->getTotalLand($dominion)) * $wizardGuildReduction),
            ($wizardGuildReductionMax / 100)
        );

        return (1 + $multiplier);
    }
}
