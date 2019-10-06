<?php

namespace OpenDominion\Calculators\Dominion;

use OpenDominion\Helpers\UnitHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Calculators\Dominion\ImprovementCalculator;

# ODA

use OpenDominion\Calculators\Dominion\MilitaryCalculator;

class CasualtiesCalculator
{
    /** @var LandCalculator */
    protected $landCalculator;

    /** @var PopulationCalculator */
    private $populationCalculator;

    /** @var SpellCalculator */
    private $spellCalculator;

    /** @var UnitHelper */
    protected $unitHelper;

    /** @var MilitaryCalculator */
    protected $militaryCalculator;

    /**
     * CasualtiesCalculator constructor.
     *
     * @param LandCalculator $landCalculator
     * @param PopulationCalculator $populationCalculator
     * @param SpellCalculator $spellCalculator
     * @param UnitHelper $unitHelper
     */
    public function __construct(LandCalculator $landCalculator, PopulationCalculator $populationCalculator, SpellCalculator $spellCalculator, UnitHelper $unitHelper, ImprovementCalculator $improvementCalculator, MilitaryCalculator $militaryCalculator)
    {
        $this->landCalculator = $landCalculator;
        $this->populationCalculator = $populationCalculator;
        $this->spellCalculator = $spellCalculator;
        $this->unitHelper = $unitHelper;
        $this->populationCalculator = $populationCalculator;
        $this->improvementCalculator = $improvementCalculator;
        $this->militaryCalculator = $militaryCalculator;
    }

    /**
     * Get the offensive casualty multiplier for a dominion for a specific unit
     * slot.
     *
     * @param Dominion $dominion
     * @param Dominion $target
     * @param int $slot
     * @param array $units Units being sent out on invasion
     * @param float $landRatio
     * @param bool $isOverwhelmed
     * @param bool $attackingForceOP
     * @param bool $targetDP
     * @return float
     */
    public function getOffensiveCasualtiesMultiplierForUnitSlot(Dominion $dominion, Dominion $target, int $slot, array $units, float $landRatio, bool $isOverwhelmed, float $attackingForceOP, float $targetDP): float
    {
        $multiplier = 1;

        # CHECK IMMORTALITY

        // Check if unit has fixed casualties first, so we can skip all other checks
        if ($dominion->race->getUnitPerkValueForUnitSlot($slot, 'fixed_casualties') !== 0)
        {
            return 1;
        }

        // If you are fighting against a does_not_kill race (Lux)
        # This means that OFFENSIVE CASUALTIES are zero when INVADING a Lux.
        if($target->race->getPerkValue('does_not_kill') == 1)
        {
          $multiplier = 0;
        }

        // Then check immortality, so we can skip the other remaining checks if we indeed have immortal units, since
        // casualties will then always be 0 anyway

        // Immortality never triggers upon being overwhelmed
        if (!$isOverwhelmed)
        {
            // General "Almost never dies" type of immortality.
            if ((bool)$dominion->race->getUnitPerkValueForUnitSlot($slot, 'immortal'))
            {
                $multiplier = 0;
            }

            // True immortality: only dies when overwhelmed.
            if ((bool)$dominion->race->getUnitPerkValueForUnitSlot($slot, 'true_immortal'))
            {
                // For now the same as SPUD-style immortal, but separate in code for future usage.
                $multiplier = 0;
            }

            // Range-based immortality
            if (($multiplier !== 0) && (($immortalVsLandRange = $dominion->race->getUnitPerkValueForUnitSlot($slot, 'immortal_vs_land_range')) !== 0))
            {
                if ($landRatio >= ($immortalVsLandRange / 100)) {
                    $multiplier = 0;
                }
            }

            // Race perk-based immortality
            if (($multiplier !== 0) && $this->isImmortalVersusRacePerk($dominion, $target, $slot))
            {
                $multiplier = 0;
            }
        }

        # END CHECK IMMORTALITY

        # CHECK UNIT AND RACIAL CASUALTY MODIFIERS

        if ($multiplier !== 0)
        {

            $nonUnitBonusMultiplier = 0;

            // Shrines
            $nonUnitBonusMultiplier += $this->getOffensiveCasualtiesReductionFromShrines($dominion);

            // Orc and Black Orc spell: increases casualties by 10%.
            if ($this->spellCalculator->isSpellActive($dominion, 'bloodrage'))
            {
              $nonUnitBonusMultiplier += -0.10;
            }

            # Troll and Lizardfolk spell: decreases casualties by 25%.
            if ($this->spellCalculator->isSpellActive($dominion, 'regeneration'))
            {
              $nonUnitBonusMultiplier += 0.25;
            }

            // Infirmary
            $nonUnitBonusMultiplier += $this->improvementCalculator->getImprovementMultiplierBonus($dominion, 'infirmary');

            // Cap $nonUnitBonusMultiplier to 80%.
            $nonUnitBonusMultiplier = min(0.80, $nonUnitBonusMultiplier);

            // Unit bonuses (multiplicative with non-unit bonuses)
            $unitBonusMultiplier = 0;

            // Unit Perk: Fewer Casualties
            $unitBonusMultiplier += ($dominion->race->getUnitPerkValueForUnitSlot($slot, ['fewer_casualties', 'fewer_casualties_offense']) / 100);

            // Unit Perk: Reduce Combat Losses
            $unitsAtHomePerSlot = [];
            $unitsAtHomeRCLSlot = null;
            $reducedCombatLosses = 0;

            // todo: inefficient to do run this code per slot. needs refactoring
            foreach ($dominion->race->units as $unit) {
                $slot = $unit->slot;
                $unitKey = "military_unit{$slot}";

                $totalUnitAmount = $dominion->$unitKey;

                $unitsAtHome = ($totalUnitAmount - ($units[$slot] ?? 0));
                $unitsAtHomePerSlot[$slot] = $unitsAtHome;

                if ($unit->getPerkValue('reduce_combat_losses') !== 0) {
                    // todo: hacky workaround for not allowing RCL for gobbos (feedback from Gabbe)
                    //  Needs to be refactored later; unit perk should be renamed in the yml to reduce_combat_losses_defense
                    if ($dominion->race->name === 'Goblin') {
                        continue;
                    }

                    $unitsAtHomeRCLSlot = $slot;
                }
            }

            // We have a unit with RCL!
            if ($unitsAtHomeRCLSlot !== null) {
                $totalUnitsAtHome = array_sum($unitsAtHomePerSlot);
                $reducedCombatLosses += (($unitsAtHomePerSlot[$unitsAtHomeRCLSlot] / $totalUnitsAtHome) / 2);
            }

            # Apply RCL do uBM.
            $unitBonusMultiplier += $reducedCombatLosses;

            // Apply to multiplier (multiplicative)
            $multiplier = ($nonUnitBonusMultiplier + $unitBonusMultiplier);

            // Absolute cap at 90% reduction.
            $multiplier = min(0.90, $multiplier);
        }

        # END CHECK UNIT AND RACIAL CASUALTY MODIFIERS

        return $multiplier;
    }

    /**
     * Get the defensive casualty multiplier for a dominion for a specific unit
     * slot.
     *
     * @param Dominion $dominion
     * @param Dominion $attacker
     * @param int|null $slot Null is for non-racial units and thus used as draftees casualties multiplier
     * @return float
     */
    public function getDefensiveCasualtiesMultiplierForUnitSlot(Dominion $dominion, Dominion $attacker, ?int $slot): float
    {
        $multiplier = 1;

        // First check immortality, so we can skip the other remaining checks if we indeed have immortal units, since
        // casualties will then always be 0 anyway

        // Only military units with a slot number could be immortal
        if ($slot !== null) {
            // Global immortality
            if ((bool)$dominion->race->getUnitPerkValueForUnitSlot($slot, 'immortal'))
            {
                if (!$this->spellCalculator->isSpellActive($attacker, 'crusade'))
                {
                    $multiplier = 0;
                }
            }
            if ((bool)$dominion->race->getUnitPerkValueForUnitSlot($slot, 'true_immortal'))
            {
                // Note: true_immortal is used for non-SPUD races to be exempt from Crusade.

                $multiplier = 0;
            }

            // Race perk-based immortality
            if (($multiplier !== 0) && $this->isImmortalVersusRacePerk($dominion, $attacker, $slot))
            {
                $multiplier = 0;
            }

        }

        // If you are fighting against a does_not_kill race (Lux)
        # This means that Defensive CASUALTIES are zero when INVADED BY a Lux.
        if($attacker->race->getPerkValue('does_not_kill') == 1)
        {
          $multiplier = 0;
        }

        if ($multiplier !== 0) {
            // Non-unit bonuses (hero, tech, wonders), capped at -80%

            $nonUnitBonusMultiplier = 0;

            // Spells
            # Troll and Lizardfolk spell: decreases casualties by 25%.
            if ($this->spellCalculator->isSpellActive($dominion, 'regeneration'))
            {
              $nonUnitBonusMultiplier += 0.25;
            }

            // Infirmary
            $nonUnitBonusMultiplier += $this->improvementCalculator->getImprovementMultiplierBonus($dominion, 'infirmary');

            // Cap $nonUnitBonusMultiplier to 80%.
            $nonUnitBonusMultiplier = min(0.80, $nonUnitBonusMultiplier);

            // Unit bonuses (multiplicative with non-unit bonuses)
            $unitBonusMultiplier = 0;

            // Unit Perk: Fewer Casualties (only on military units with a slot, draftees don't have this perk)
            if ($slot)
            {
                $unitBonusMultiplier += ($dominion->race->getUnitPerkValueForUnitSlot($slot, ['fewer_casualties', 'fewer_casualties_defense']) / 100);
            }

            // Unit Perk: Reduce Combat Losses
            $unitsAtHomePerSlot = [];
            $unitsAtHomeRCLSlot = null;
            $reducedCombatLosses = 0;

            // todo: inefficient to do run this code per slot. needs refactoring
            foreach ($dominion->race->units as $unit) {
                $slot = $unit->slot;
                $unitKey = "military_unit{$slot}";

                $unitsAtHomePerSlot[$slot] = $dominion->$unitKey;

                if ($unit->getPerkValue('reduce_combat_losses') !== 0) {
                    $unitsAtHomeRCLSlot = $slot;
                }
            }

            // We have a unit with RCL!
            if ($unitsAtHomeRCLSlot !== null) {
                $totalUnitsAtHome = array_sum($unitsAtHomePerSlot);

                if ($totalUnitsAtHome > 0) {
                    $reducedCombatLosses += (($unitsAtHomePerSlot[$unitsAtHomeRCLSlot] / $totalUnitsAtHome) / 2);
                }
            }

            # Apply RCL do uBM.

            $unitBonusMultiplier += $reducedCombatLosses;

            // Apply to multiplier (multiplicative)
            $multiplier = ($nonUnitBonusMultiplier + $unitBonusMultiplier);

            // Absolute cap at 90% reduction.
            $multiplier = min(0.90, $multiplier);
        }

        return $multiplier;
    }

    /**
     * Returns the Dominion's offensive casualties reduction from shrines.
     *
     * This number is in the 0 - 0.8 range, where 0 is no casualty reduction
     * (0%) and 0.8 is full (-80%). Used additive in a multiplier formula.
     *
     * @param Dominion $dominion
     * @return float
     */
    public function getOffensiveCasualtiesReductionFromShrines(Dominion $dominion): float
    {
        // Values (percentage)
        $casualtyReductionPerShrine = 5.5;
        $maxCasualtyReductionFromShrines = 80;

        return min(
            (($casualtyReductionPerShrine * $dominion->building_shrine) / $this->landCalculator->getTotalLand($dominion)),
            ($maxCasualtyReductionFromShrines / 100)
        );
    }

    /**
     * Returns the Dominion's casualties by unit type.
     *
     * @param  Dominion $dominion
     * @param int $foodDeficit
     * @return array
     */
    public function getStarvationCasualtiesByUnitType(Dominion $dominion, int $foodDeficit): array
    {
        $units = $this->getStarvationUnitTypes();

        $totalCasualties = $this->getTotalStarvationCasualties($dominion, $foodDeficit);

        if ($totalCasualties === 0) {
            return [];
        }

        $peasantPopPercentage = $dominion->peasants / $this->populationCalculator->getPopulation($dominion);
        $casualties = ['peasants' => min($totalCasualties * $peasantPopPercentage, $dominion->peasants)];
        $casualties += array_fill_keys($units, 0);

        $remainingCasualties = ($totalCasualties - array_sum($casualties));

        while (count($units) > 0 && $remainingCasualties > 0) {
            foreach ($units as $unit) {
                $casualties[$unit] = (int)min(
                    (array_get($casualties, $unit, 0) + (int)(ceil($remainingCasualties / count($units)))),
                    $dominion->{$unit}
                );
            }

            $remainingCasualties = $totalCasualties - array_sum($casualties);

            $units = array_filter($units, function ($unit) use ($dominion, $casualties) {
                return ($casualties[$unit] < $dominion->{$unit});
            });
        }

        if ($remainingCasualties < 0) {
            while ($remainingCasualties < 0) {
                foreach (array_keys(array_reverse($casualties)) as $unitType) {
                    if ($casualties[$unitType] > 0) {
                        $casualties[$unitType]--;
                        $remainingCasualties++;
                    }

                    if ($remainingCasualties === 0) {
                        break 2;
                    }
                }
            }
        } elseif ($remainingCasualties > 0) {
            $casualties['peasants'] = (int)min(
                ($remainingCasualties + $casualties['peasants']),
                $dominion->peasants
            );
        }

        $casualties = array(
          'peasants' => 0,
          'unit1' => 0,
          'unit2' => 0,
          'unit3' => 0,
          'unit4' => 0,
          'spies' => 0,
          'wizards' => 0,
          'archmage' => 0
        );

      return array_filter($casualties);
    }

    /**
     * Returns the Dominion's number of casualties due to starvation.
     *
     * @param  Dominion $dominion
     * @param int $foodDeficit
     * @return int
     */
    public function getTotalStarvationCasualties(Dominion $dominion, int $foodDeficit): int
    {
        if ($foodDeficit >= 0) {
            return 0;
        }

        $casualties = (int)(abs($foodDeficit) * 2);
        $maxCasualties = $this->populationCalculator->getPopulation($dominion) * 0.02;

        return min($casualties, $maxCasualties);
    }

    /**
     * Returns the unit types that can suffer casualties.
     *
     * @return array
     */
    protected function getStarvationUnitTypes(): array
    {
        return array_merge(
            array_map(
                function ($unit) {
                    return ('military_' . $unit);
                },
                $this->unitHelper->getUnitTypes()
            ),
            ['military_draftees']
        );
    }

    /**
     * @param Dominion $dominion
     * @param Dominion $target
     * @param int $slot
     * @return bool
     */
    protected function isImmortalVersusRacePerk(Dominion $dominion, Dominion $target, int $slot): bool
    {
        $raceNameFormatted = strtolower($target->race->name);
        $raceNameFormatted = str_replace(' ', '_', $raceNameFormatted);

        $perkValue = $dominion->race->getUnitPerkValueForUnitSlot($slot, 'immortal_except_vs');

        if(!$perkValue)
        {
            return false;
        }

        return $perkValue !== $raceNameFormatted;
    }
}
