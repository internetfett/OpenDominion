<?php

namespace OpenDominion\HeroCombat\Analysis;

use InvalidArgumentException;
use OpenDominion\Calculators\Dominion\HeroCalculator;
use OpenDominion\HeroCombat\Content\Loadouts\HeroClassLoadouts;
use OpenDominion\HeroCombat\Content\Loadouts\LoadoutOption;
use OpenDominion\HeroCombat\Content\Loadouts\LoadoutSlot;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\HeroCombat\Registry\CombatRegistry;

/**
 * Parses build specs and turns builds into in-memory combatants, reading options from the
 * combat registry (no database needed).
 *
 * Spec format: "level=9;technique=backstab;ability=shadow_strike;gear=greatsword,plate_armor"
 *
 * A build's level stands in for every class level, so any class signature is open at level 5.
 */
class BuildFactory
{
    public function __construct(
        private HeroCalculator $heroCalculator,
        private CombatRegistry $registry,
    ) {
    }

    public function parse(string $spec): Build
    {
        $values = ['level' => '9', 'technique' => '', 'ability' => '', 'gear' => ''];

        foreach (array_filter(explode(';', $spec)) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $key = trim($key);
            if (!array_key_exists($key, $values)) {
                throw new InvalidArgumentException("Unknown build option '{$key}'");
            }
            $values[$key] = trim($value);
        }

        $techniques = $this->keys($values['technique']);
        $abilities = $this->keys($values['ability']);
        $gear = $this->keys($values['gear']);
        $level = (int) $values['level'];

        $this->validate(LoadoutSlot::Technique, $techniques, $level);
        $this->validate(LoadoutSlot::Ability, $abilities, $level);
        $this->validate(LoadoutSlot::Gear, $gear, $level);

        if (!$this->heroCalculator->gearKeepsEvasion(array_map(fn (string $key) => $this->registry->loadoutOption($key), $gear))) {
            throw new InvalidArgumentException('That gear would take evasion below 0');
        }

        return new Build(
            level: $level,
            technique: $techniques[0] ?? null,
            ability: $abilities[0] ?? null,
            gear: $gear,
        );
    }

    /**
     * Option keys for a slot available at or below a level, or only at exactly $level.
     *
     * @return string[]
     */
    public function optionsOfType(string $type, int $maxLevel = 99, ?int $level = null): array
    {
        return array_keys(array_filter(
            $this->registry->loadoutOptions(LoadoutSlot::from($type)),
            fn (LoadoutOption $option) => $option->level <= $maxLevel && ($level === null || $option->level === $level),
        ));
    }

    /**
     * @return array<string, int> keyed by Stat value
     */
    public function stats(Build $build): array
    {
        $stats = $this->heroCalculator->getBaseCombatStats($build->level);

        foreach ($build->gear as $gearKey) {
            foreach ($this->registry->loadoutOption($gearKey)->stats as $stat => $value) {
                $stats[$stat] += $value;
            }
        }

        return $stats;
    }

    /**
     * Adds a hero with this build to the battle.
     */
    public function addTo(Battle $battle, Build $build, int $id, int $team, string $name, string $strategy): CombatantState
    {
        $stats = $this->stats($build);
        $picks = array_filter([$build->technique, $build->ability]);
        $grants = array_filter($picks, fn (string $key) => $battle->registry->hasAbility($key));
        $combatant = new CombatantState(
            id: $id,
            name: $name,
            team: $team,
            baseStats: $stats,
            currentHealth: $stats[Stat::Health->value],
            abilities: (new HeroClassLoadouts())->withGrants(HeroClassLoadouts::BASIC_ABILITIES, $grants),
            ai: $strategy,
            heroId: $id,
            dominionId: $id,
        );
        $combatant->automated = true;
        $battle->state->addCombatant($combatant);

        foreach ($picks as $key) {
            if ($battle->registry->hasEffect($key)) {
                $battle->effects->apply($combatant, $key);
            }
        }

        return $combatant;
    }

    /**
     * @return string[]
     */
    private function keys(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    /**
     * @param string[] $keys
     */
    private function validate(LoadoutSlot $slot, array $keys, int $level): void
    {
        if (count($keys) > $slot->capacityAt($level)) {
            throw new InvalidArgumentException("A level {$level} build can only bring {$slot->capacityAt($level)} {$slot->value}");
        }

        if (count($keys) !== count(array_unique($keys))) {
            throw new InvalidArgumentException("A build can only bring one of each {$slot->value}");
        }

        foreach ($keys as $key) {
            if (!$this->registry->hasLoadoutOption($key) || $this->registry->loadoutOption($key)->slot !== $slot) {
                throw new InvalidArgumentException("'{$key}' is not a {$slot->value}");
            }

            $requiredLevel = $this->registry->loadoutOption($key)->level;
            if ($requiredLevel > $level) {
                throw new InvalidArgumentException("'{$key}' requires level {$requiredLevel}");
            }
        }
    }
}
