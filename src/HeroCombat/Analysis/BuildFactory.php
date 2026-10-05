<?php

namespace OpenDominion\HeroCombat\Analysis;

use InvalidArgumentException;
use OpenDominion\Calculators\Dominion\HeroCalculator;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses build specs and turns builds into in-memory combatants, reading upgrade data
 * straight from app/data/heroes.yml (no database needed).
 *
 * Spec format: "level=9;discipline=riposte;form=feint;gear=greatsword,plate_armor"
 */
class BuildFactory
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $upgrades = null;

    public function __construct(private HeroCalculator $heroCalculator)
    {
    }

    public function parse(string $spec): Build
    {
        $values = ['level' => '9', 'discipline' => null, 'form' => '', 'gear' => ''];

        foreach (array_filter(explode(';', $spec)) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $key = trim($key);
            if (!array_key_exists($key, $values)) {
                throw new InvalidArgumentException("Unknown build option '{$key}'");
            }
            $values[$key] = trim($value);
        }

        $build = new Build(
            level: (int) $values['level'],
            discipline: $values['discipline'] ?: null,
            forms: array_values(array_filter(explode(',', (string) $values['form']))),
            gear: array_values(array_filter(explode(',', (string) $values['gear']))),
        );

        $this->validate($build);

        return $build;
    }

    /**
     * Upgrade keys of a type available at or below a level.
     *
     * @return string[]
     */
    public function optionsOfType(string $type, int $maxLevel = 99): array
    {
        return array_keys(array_filter(
            $this->upgrades(),
            fn (array $upgrade) => $upgrade['type'] === $type && $upgrade['level'] <= $maxLevel,
        ));
    }

    /**
     * @return array<string, int> keyed by Stat value
     */
    public function stats(Build $build): array
    {
        $stats = $this->heroCalculator->getBaseCombatStats($build->level);

        foreach ($build->gear as $gearKey) {
            foreach ($this->upgrades()[$gearKey]['perks'] ?? [] as $perk => $value) {
                $stat = substr($perk, strlen('combat_'));
                if (str_starts_with($perk, 'combat_') && isset($stats[$stat])) {
                    $stats[$stat] += $value;
                }
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
        $combatant = new CombatantState(
            id: $id,
            name: $name,
            team: $team,
            baseStats: $stats,
            currentHealth: $stats[Stat::Health->value],
            abilities: ['attack', 'defend', 'focus', 'counter', 'recover'],
            ai: $strategy,
            heroId: $id,
            dominionId: $id,
        );
        $combatant->automated = true;
        $battle->state->addCombatant($combatant);

        foreach (array_filter(array_merge([$build->discipline], $build->forms)) as $technique) {
            $battle->effects->apply($combatant, $technique);
        }

        return $combatant;
    }

    private function validate(Build $build): void
    {
        $picks = array_filter(array_merge(
            ['discipline' => $build->discipline],
            array_combine(array_map(fn ($i) => "form {$i}", array_keys($build->forms)), $build->forms),
            array_combine(array_map(fn ($i) => "gear {$i}", array_keys($build->gear)), $build->gear),
        ));

        foreach ($picks as $slot => $key) {
            $type = explode(' ', $slot)[0];
            $upgrade = $this->upgrades()[$key] ?? null;

            if ($upgrade === null || $upgrade['type'] !== $type) {
                throw new InvalidArgumentException("'{$key}' is not a {$type}");
            }
            if ($upgrade['level'] > $build->level) {
                throw new InvalidArgumentException("'{$key}' requires level {$upgrade['level']}");
            }
        }

        $levels = array_map(fn ($key) => $this->upgrades()[$key]['level'], array_merge($build->forms, $build->gear));
        if (count($levels) !== count(array_unique($levels))) {
            throw new InvalidArgumentException('A build can only take one upgrade per level');
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function upgrades(): array
    {
        return $this->upgrades ??= Yaml::parseFile(base_path('app/data/heroes.yml'));
    }
}
