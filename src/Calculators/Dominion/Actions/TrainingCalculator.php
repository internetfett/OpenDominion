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
                    $champion = $units[$unitSlot]->cost_champion;
                    $soul = $units[$unitSlot]->cost_soul;

                    $unit1 = $units[$unitSlot]->cost_unit1;
                    $unit2 = $units[$unitSlot]->cost_unit2;
                    $unit3 = $units[$unitSlot]->cost_unit3;
                    $unit4 = $units[$unitSlot]->cost_unit4;

                    if ($platinum > 0) {
                        $cost['platinum'] = (int)ceil($platinum * $this->getSpecialistEliteCostMultiplier($dominion, 'platinum'));
                    }

                    if ($ore > 0) {
                        $cost['ore'] = $ore;
                        $cost['ore'] = (int)ceil($ore * $this->getSpecialistEliteCostMultiplier($dominion, 'ore'));
                    }

                    // FOOD cost for units
                    if ($food > 0) {
                        $cost['food'] = $food;
                        $cost['food'] = (int)ceil($food * $this->getSpecialistEliteCostMultiplier($dominion, 'ore'));
                    }
                    // MANA cost for units
                    if ($mana > 0) {
                        $cost['mana'] = $mana;
                        $cost['mana'] = (int)ceil($mana * $this->getSpecialistEliteCostMultiplier($dominion, 'mana'));
                    }
                    // GEM cost for units
                    if ($gem > 0) {
                        $cost['gem'] = $gem;
                        $cost['gem'] = (int)ceil($gem * $this->getSpecialistEliteCostMultiplier($dominion, 'gem'));
                    }
                    // LUMBER cost for units
                    if ($lumber > 0) {
                        $cost['lumber'] = $lumber;
                        $cost['lumber'] = (int)ceil($lumber * $this->getSpecialistEliteCostMultiplier($dominion, 'lumber'));
                    }
                    // PRESTIGE cost for units
                    if ($prestige > 0) {
                        $cost['prestige'] = $prestige;
                        $cost['prestige'] = (int)ceil($prestige * $this->getSpecialistEliteCostMultiplier($dominion, 'prestige'));
                    }

                    // BOAT cost for units
                    if ($boat > 0) {
                        $cost['boat'] = $boat;
                        $cost['boat'] = (int)ceil($boat * $this->getSpecialistEliteCostMultiplier($dominion, 'boat'));
                    }

                    // CHAMPION cost for units
                    if ($champion > 0) {
                        $cost['champion'] = $champion;
                        $cost['champion'] = (int)ceil($champion * $this->getSpecialistEliteCostMultiplier($dominion, 'champion'));
                    }

                    // SOUL cost for units
                    if ($soul > 0) {
                        $cost['soul'] = $soul;
                        $cost['soul'] = (int)ceil($soul * $this->getSpecialistEliteCostMultiplier($dominion, 'soul'));
                    }

                    // UNIT1 cost for units
                    if ($unit1 > 0) {
                        $cost['unit1'] = $unit1;
                        $cost['unit1'] = (int)ceil($unit1 * $this->getSpecialistEliteCostMultiplier($dominion, 'unit1'));
                    }

                    // UNIT2 cost for units
                    if ($unit2 > 0) {
                        $cost['unit2'] = $unit2;
                        $cost['unit2'] = (int)ceil($unit2 * $this->getSpecialistEliteCostMultiplier($dominion, 'unit2'));
                    }

                    // UNIT3 cost for units
                    if ($unit3 > 0) {
                        $cost['unit3'] = $unit3;
                        $cost['unit3'] = (int)ceil($unit3 * $this->getSpecialistEliteCostMultiplier($dominion, 'unit3'));
                    }

                    // UNIT4 cost for units
                    if ($unit4 > 0) {
                        $cost['unit4'] = $unit4;
                        $cost['unit4'] = (int)ceil($unit4 * $this->getSpecialistEliteCostMultiplier($dominion, 'unit4'));
                    }

                    #if ($dominion->race->getUnitPerkValueForUnitSlot($slot, 'fixed_casualties') !== 0)

                    if($dominion->race->getUnitPerkValueForUnitSlot(intval(str_replace('unit','',$unitType)), 'no_draftee') == 1)
                    {
                      $cost['draftees'] = 0;
                    }
                    else
                    {
                      $cost['draftees'] = 1;
                    }

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
            'champion' => 'resource_champion',
            'soul' => 'resource_soul',

            'unit1' => 'military_unit1',
            'unit2' => 'military_unit2',
            'unit3' => 'military_unit3',
            'unit4' => 'military_unit4',
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

                if($type == 'draftees' and $value == 0)
                {
                  $value = 1; # Ugly, causes display bug
                }

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
     * @param string $resourceType
     * @return float
     */
    public function getSpecialistEliteCostMultiplier(Dominion $dominion, string $resourceType): float
    {
        $multiplier = 0;

        // Values (percentages)
        $smithiesReduction = 2;
        $smithiesReductionMax = 40;

        // Never discount these resources.
        $exemptResourceTypes = array('mana','food','gem','boat','prestige','champion','soul','unit1','unit2','unit3','unit4');

        // Smithies
        $exemptRaces = array('Gnome', 'Imperial Gnome');

        # Apply smithies to non-exempt resources (to platinum and ore)
        if(!in_array($resourceType,$exemptResourceTypes))
        {
          $multiplier -= min(
              (($dominion->building_smithy / $this->landCalculator->getTotalLand($dominion)) * $smithiesReduction),
              ($smithiesReductionMax / 100)
          );

          // Start multiplier back to zero if Ore and a Gnomish race.
          if($resourceType == 'ore' and in_array($dominion->race->name, $exemptRaces))
          {
            $multiplier = 0;
          }

        }

        // Armory
        if(!in_array($resourceType,$exemptResourceTypes))
        {
          if($this->improvementCalculator->getImprovementMultiplierBonus($dominion, 'armory') > 0)
          {
              $multiplier -= $this->improvementCalculator->getImprovementMultiplierBonus($dominion, 'armory');
          }
        }
        // todo: Master of Resources Tech

        // Cap $multiplier at -50%
        $multiplier = max($multiplier, -0.50);

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
