<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use Closure;
use OpenDominion\HeroCombat\Content\Loadouts\HeroClassLoadouts;
use OpenDominion\HeroCombat\Content\Loadouts\LoadoutSlot;
use OpenDominion\HeroCombat\Engine\ActionValidator;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\TurnResolver;
use OpenDominion\HeroCombat\Registry\CombatRegistry;
use OpenDominion\Providers\HeroCombatServiceProvider;
use OpenDominion\Tests\Unit\HeroCombat\Support\BattleBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Plays the same battles twice, the second time with every combatant's id reversed, and checks
 * that each turn ends the same way. Actions resolve in id order, so any difference means a turn
 * isn't truly simultaneous. Evasion is decided per target per turn, so it can't depend on the
 * order hits land in.
 */
class TurnOrderIndependenceTest extends TestCase
{
    private const TURNS = 8;

    private const EVASION = 25;

    private static ?CombatRegistry $registry = null;

    /**
     * @return array<string, array{0: string}>
     */
    public static function loadoutOptions(): array
    {
        $cases = [];
        foreach (self::registry()->loadoutOptions() as $key => $option) {
            if ($option->slot !== LoadoutSlot::Gear) {
                $cases[$key] = [$key];
            }
        }

        return $cases;
    }

    public function testBaseActionsDoNotDependOnIdOrder(): void
    {
        $this->assertOrderIndependent(fn () => [], battles: 150, seed: 1);
    }

    #[DataProvider('loadoutOptions')]
    public function testLoadoutOptionDoesNotDependOnIdOrder(string $key): void
    {
        $this->assertOrderIndependent(fn () => [$key], battles: 40, seed: crc32($key));
    }

    public function testMixedLoadoutsDoNotDependOnIdOrder(): void
    {
        $techniques = array_keys(self::registry()->loadoutOptions(LoadoutSlot::Technique));
        $abilities = array_keys(self::registry()->loadoutOptions(LoadoutSlot::Ability));
        $gear = array_keys(self::registry()->loadoutOptions(LoadoutSlot::Gear));

        $this->assertOrderIndependent(fn (Randomizer $random) => [
            $techniques[$random->getInt(0, count($techniques) - 1)],
            $abilities[$random->getInt(0, count($abilities) - 1)],
            $gear[$random->getInt(0, count($gear) - 1)],
        ], battles: 300, seed: 7);
    }

    /**
     * @param Closure(Randomizer): string[] $loadout picks one hero's loadout keys
     */
    private function assertOrderIndependent(Closure $loadout, int $battles, int $seed): void
    {
        for ($i = 0; $i < $battles; $i++) {
            $random = new Randomizer(new Mt19937($seed + $i));
            $teams = $i % 2 === 0 ? ['H1' => 1, 'F1' => 2] : ['H1' => 1, 'H2' => 1, 'F1' => 2, 'F2' => 2];

            $heroes = [];
            foreach ($teams as $name => $team) {
                $heroes[$name] = ['team' => $team, 'health' => $random->getInt(25, 100), 'loadout' => $loadout($random)];
            }

            $this->playBothWays($heroes, $seed + $i);
        }
    }

    /**
     * @param array<string, array{team: int, health: int, loadout: string[]}> $heroes
     */
    private function playBothWays(array $heroes, int $seed): void
    {
        $names = array_keys($heroes);
        $forward = $this->build($heroes, $names);
        $reversed = $this->build($heroes, array_reverse($names));

        for ($turn = 1; $turn <= self::TURNS && !$forward->state->finished; $turn++) {
            $random = new Randomizer(new Mt19937($seed * 100 + $turn));
            $picks = $this->choose($forward, $random);
            $rolls = [];
            foreach ($names as $name) {
                $rolls[$name] = $random->getInt(0, 99);
            }

            $before = $this->snapshot($forward);
            foreach ([$forward, $reversed] as $battle) {
                foreach ($picks as $name => [$ability, $target]) {
                    $this->named($battle, $name)->queue = [[
                        'ability' => $ability,
                        'target' => $target === null ? null : $this->named($battle, $target)->id,
                    ]];
                }
                $battle->evasionRoll = fn (CombatantState $target, int $evasion) => $rolls[$target->name] < $evasion;
                (new TurnResolver())->resolve($battle);
            }

            $this->assertSame($this->snapshot($forward), $this->snapshot($reversed), json_encode([
                'seed' => $seed,
                'turn' => $turn,
                'loadouts' => array_map(fn ($hero) => $hero['loadout'], $heroes),
                'picks' => $picks,
                'evaded' => array_keys(array_filter($rolls, fn ($roll) => $roll < self::EVASION)),
                'before' => $before,
            ], JSON_PRETTY_PRINT));
        }
    }

    /**
     * @param array<string, array{team: int, health: int, loadout: string[]}> $heroes
     * @param string[] $order
     */
    private function build(array $heroes, array $order): Battle
    {
        $registry = self::registry();
        $builder = BattleBuilder::make()->withRegistry($registry);

        foreach ($order as $name) {
            $stats = ['evasion' => self::EVASION];
            $grants = [];
            foreach ($heroes[$name]['loadout'] as $key) {
                $option = $registry->loadoutOption($key);
                foreach ($option->stats as $stat => $amount) {
                    $stats[$stat] = ($stats[$stat] ?? BattleBuilder::BASE_STATS[$stat]) + $amount;
                }
                if ($registry->hasAbility($key)) {
                    $grants[] = $key;
                }
            }
            $abilities = (new HeroClassLoadouts())->withGrants(HeroClassLoadouts::BASIC_ABILITIES, $grants);
            $builder->hero($name, team: $heroes[$name]['team'], stats: $stats, abilities: $abilities);
        }

        $battle = $builder->build();
        foreach ($order as $name) {
            $hero = $this->named($battle, $name);
            $hero->currentHealth = $heroes[$name]['health'];
            foreach ($heroes[$name]['loadout'] as $key) {
                if (!$registry->hasAbility($key) && $registry->hasEffect($key)) {
                    $battle->effects->apply($hero, $key);
                }
            }
        }

        return $battle;
    }

    /**
     * A random usable action and target for every living combatant, by name.
     *
     * @return array<string, array{0: string, 1: ?string}>
     */
    private function choose(Battle $battle, Randomizer $random): array
    {
        $validator = new ActionValidator($battle);
        $living = $this->byName($battle->living());
        $picks = [];

        foreach ($living as $actor) {
            $usable = array_values(array_filter($actor->abilities, fn (string $key) => $validator->canPerform($actor, $key)));
            sort($usable);
            $key = $usable[$random->getInt(0, count($usable) - 1)];
            $ability = $battle->registry->ability($key);

            $target = null;
            if ($ability->targetRule()->needsChosenTarget()) {
                $options = array_values(array_filter($living, fn (CombatantState $candidate) => $battle->targets->isValidChoice($actor, $ability, $candidate)));
                $target = $options === [] ? null : $options[$random->getInt(0, count($options) - 1)]->name;
            }

            $picks[$actor->name] = [$key, $target];
        }

        return $picks;
    }

    /**
     * Everything a turn can change, keyed by name rather than id.
     *
     * @return array<string, mixed>
     */
    private function snapshot(Battle $battle): array
    {
        $snapshot = ['finished' => $battle->state->finished, 'winning_team' => $battle->state->winningTeam];

        foreach ($this->byName($battle->combatants()) as $combatant) {
            $effects = [];
            foreach ($combatant->effects as $instance) {
                $data = array_filter($instance->data, fn ($value) => is_scalar($value));
                ksort($data);
                $effects[] = "{$instance->key} x{$instance->stacks} turns:" . ($instance->remainingTurns ?? '-') . ' ' . json_encode($data);
            }
            sort($effects);
            $cooldowns = $combatant->cooldowns;
            ksort($cooldowns);
            $charges = $combatant->charges;
            ksort($charges);

            $snapshot[$combatant->name] = [
                'health' => $combatant->currentHealth,
                'last_action' => $combatant->lastAction,
                'cooldowns' => $cooldowns,
                'charges' => $charges,
                'effects' => $effects,
            ];
        }

        return $snapshot;
    }

    /**
     * @param CombatantState[] $combatants
     * @return CombatantState[]
     */
    private function byName(array $combatants): array
    {
        usort($combatants, fn (CombatantState $a, CombatantState $b) => strcmp($a->name, $b->name));

        return $combatants;
    }

    private function named(Battle $battle, string $name): CombatantState
    {
        foreach ($battle->combatants() as $combatant) {
            if ($combatant->name === $name) {
                return $combatant;
            }
        }

        $this->fail("No combatant named {$name}");
    }

    private static function registry(): CombatRegistry
    {
        return self::$registry ??= HeroCombatServiceProvider::buildRegistry();
    }
}
