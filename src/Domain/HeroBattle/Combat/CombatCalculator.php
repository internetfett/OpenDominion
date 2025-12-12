<?php

namespace OpenDominion\Domain\HeroBattle\Combat;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesAttack;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesCounter;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesDefense;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesEvasion;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\ModifiesRecovery;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;
use OpenDominion\Models\Hero;
use OpenDominion\Models\HeroCombatant;

/**
 * Combat Calculator
 *
 * Handles all combat-related calculations for hero battles.
 * Uses ability traits instead of hardcoded checks.
 */
class CombatCalculator
{
    /**
     * Get base combat stats for a given level
     */
    public function getBaseCombatStats(int $level = 0): array
    {
        return [
            'health' => 80 + (5 * $level),
            'attack' => 40,
            'defense' => 20,
            'evasion' => 10,
            'focus' => 10,
            'counter' => 10,
            'recover' => 20,
        ];
    }

    /**
     * Get hero combat stats including perks
     */
    public function getHeroCombatStats(Hero $hero): array
    {
        $heroCalculator = app('OpenDominion\Calculators\Dominion\HeroCalculator');
        $level = $heroCalculator->getHeroLevel($hero);

        if ($hero->class_data !== null) {
            // Combat stats based on highest level class
            $level = max($level, collect($hero->class_data)->max('level'));
        }

        $combatStats = $this->getBaseCombatStats($level);

        foreach ($combatStats as $stat => $value) {
            $combatStats[$stat] += $hero->getPerkValue("combat_{$stat}");
        }

        return $combatStats;
    }

    /**
     * Calculate attack stat with ability modifiers
     *
     * @param HeroCombatant $combatant
     * @param Collection $abilities Abilities that implement ModifiesAttack
     * @return int
     */
    public function calculateAttack(HeroCombatant $combatant, Collection $abilities): int
    {
        $attack = $combatant->attack;

        // Apply ability modifiers
        foreach ($abilities as $ability) {
            if ($ability instanceof ModifiesAttack) {
                $attack = $ability->modifyAttack($combatant, $attack);
            }
        }

        return (int) round($attack);
    }

    /**
     * Calculate defense stat with ability modifiers
     *
     * @param HeroCombatant $combatant
     * @param Collection $abilities Abilities that implement ModifiesDefense
     * @return int
     */
    public function calculateDefense(HeroCombatant $combatant, Collection $abilities): int
    {
        $defense = $combatant->defense;

        // Apply ability modifiers
        foreach ($abilities as $ability) {
            if ($ability instanceof ModifiesDefense) {
                $defense = $ability->modifyDefense($combatant, $defense);
            }
        }

        return (int) round($defense);
    }

    /**
     * Calculate evasion stat with ability modifiers
     *
     * @param HeroCombatant $combatant
     * @param Collection $abilities Abilities that implement ModifiesEvasion
     * @return int
     */
    public function calculateEvasion(HeroCombatant $combatant, Collection $abilities): int
    {
        $evasion = $combatant->evasion;

        // Apply ability modifiers
        foreach ($abilities as $ability) {
            if ($ability instanceof ModifiesEvasion) {
                $evasion = $ability->modifyEvasion($combatant, $evasion);
            }
        }

        return (int) round($evasion);
    }

    /**
     * Calculate recovery stat with ability modifiers
     *
     * @param HeroCombatant $combatant
     * @param Collection $abilities Abilities that implement ModifiesRecovery
     * @return int
     */
    public function calculateRecovery(HeroCombatant $combatant, Collection $abilities): int
    {
        $recovery = $combatant->recover;

        // Apply ability modifiers
        foreach ($abilities as $ability) {
            if ($ability instanceof ModifiesRecovery) {
                $recovery = $ability->modifyRecovery($combatant, $recovery);
            }
        }

        return (int) round($recovery);
    }

    /**
     * Calculate counter stat with ability modifiers
     *
     * @param HeroCombatant $combatant
     * @param Collection $abilities Abilities that implement ModifiesCounter
     * @return int
     */
    public function calculateCounter(HeroCombatant $combatant, Collection $abilities): int
    {
        $counter = $combatant->counter;

        // Apply ability modifiers
        foreach ($abilities as $ability) {
            if ($ability instanceof ModifiesCounter) {
                $counter = $ability->modifyCounter($combatant, $counter);
            }
        }

        return (int) round($counter);
    }

    /**
     * Calculate combat damage
     *
     * @param HeroCombatant $combatant
     * @param HeroCombatant $target
     * @param array $actionDef
     * @param Collection $combatantAbilities
     * @param Collection $targetAbilities
     * @return int
     */
    public function calculateCombatDamage(
        HeroCombatant $combatant,
        HeroCombatant $target,
        array $actionDef,
        Collection $combatantAbilities,
        Collection $targetAbilities
    ): int {
        $baseDamage = $this->calculateAttack($combatant, $combatantAbilities);
        $baseDefense = $this->calculateDefense($target, $targetAbilities);
        $defendModifier = $actionDef['attributes']['defend'] ?? 0;
        $bonusDamage = $actionDef['attributes']['bonus_damage'] ?? 0;

        // Add counter bonus if countering
        if ($combatant->current_action == 'counter') {
            $baseDamage += $combatant->counter;
        }

        // Add focus bonus if focused
        if ($combatant->has_focus) {
            $baseDamage += $combatant->focus;
        }

        // Add bonus damage from action
        $baseDamage += $bonusDamage;

        // Target action modifiers
        if ($target->current_action == 'recover') {
            $baseDefense -= 5;
        }

        if ($target->current_action == 'defend') {
            $baseDefense *= 2;
            $baseDefense += $defendModifier;
        }

        $damage = max(0, $baseDamage - $baseDefense);

        return (int) round($damage);
    }

    /**
     * Calculate combat evasion check
     *
     * @param HeroCombatant $target
     * @param array $actionDef
     * @param Collection $targetAbilities
     * @return bool
     */
    public function calculateCombatEvade(
        HeroCombatant $target,
        array $actionDef,
        Collection $targetAbilities
    ): bool {
        // Some actions have forced evasion behavior
        $evaded = $actionDef['attributes']['evade'] ?? null;
        if ($evaded !== null) {
            return $evaded;
        }

        $evasion = $this->calculateEvasion($target, $targetAbilities);
        return mt_rand(0, 100) < $evasion;
    }

    /**
     * Calculate combat healing amount
     *
     * @param HeroCombatant $combatant
     * @param Collection $combatantAbilities
     * @return int
     */
    public function calculateCombatHeal(
        HeroCombatant $combatant,
        Collection $combatantAbilities
    ): int {
        return $this->calculateRecovery($combatant, $combatantAbilities);
    }
}
