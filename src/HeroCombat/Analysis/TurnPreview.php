<?php

namespace OpenDominion\HeroCombat\Analysis;

use OpenDominion\HeroCombat\Content\Effects\AbstractStatShift;
use OpenDominion\HeroCombat\Content\Effects\Marked;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\InMemorySpawner;
use OpenDominion\HeroCombat\Engine\Stats\ModifierType;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\HeroCombat\Engine\TurnResolver;
use OpenDominion\HeroCombat\Persistence\StateSnapshot;

/**
 * Simulates one turn on a copy of a battle with chosen actions, enumerating every evasion
 * outcome and weighting it by its probability. The copy is made from a StateSnapshot,
 * which carries no queued actions or intents, so nothing private leaks into a preview.
 */
final class TurnPreview
{
    /** Evasion rolls use int(0, 100) < evasion: 101 equally likely values. */
    private const ROLL_RANGE = 101;

    /** Share of a combatant's counter damage counted as threat (Counter is about a fifth of play). */
    public const COUNTER_WEIGHT = 0.25;

    /** Extra turns a battle-long stat shift is expected to matter. */
    public const LASTING_TURNS = 4;

    /** @var array<string, TurnOutcome> outcomes for the current battle turn only */
    private array $cache = [];

    private ?string $cacheContext = null;

    private StateSnapshot $snapshot;

    public function __construct()
    {
        $this->snapshot = new StateSnapshot();
    }

    /**
     * @param array<int, string> $actions combatant id => ability key; targets are chosen
     *                                    automatically (the single enemy, or yourself)
     */
    public function outcome(Battle $battle, array $actions): TurnOutcome
    {
        $context = spl_object_id($battle->state) . ':' . $battle->turn();
        if ($context !== $this->cacheContext) {
            $this->cache = [];
            $this->cacheContext = $context;
        }

        ksort($actions);
        $snapshot = $this->snapshot->capture($battle->state);
        $cacheKey = md5(json_encode([$snapshot, $actions]));

        return $this->cache[$cacheKey] ??= $this->simulate($battle, $snapshot, $actions);
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * @param array<string, mixed> $snapshot
     * @param array<int, string> $actions
     */
    private function simulate(Battle $battle, array $snapshot, array $actions): TurnOutcome
    {
        $branches = [[]];
        $results = [];

        while ($branches !== []) {
            $decisions = array_shift($branches);
            [$copy, $probability, $checks] = $this->run($battle, $snapshot, $actions, $decisions);

            if ($checks > count($decisions)) {
                $branches[] = array_merge($decisions, [true]);
                $branches[] = array_merge($decisions, [false]);
                continue;
            }

            $results[] = [$copy, $probability];
        }

        $total = array_sum(array_column($results, 1)) ?: 1;
        $health = [];
        $offense = [];
        $deaths = [];

        foreach ($results as [$copy, $probability]) {
            $weight = $probability / $total;
            foreach ($copy->combatants() as $combatant) {
                $health[$combatant->id] = ($health[$combatant->id] ?? 0) + $weight * $combatant->currentHealth;
                $offense[$combatant->id] = ($offense[$combatant->id] ?? 0) + $weight * self::offense($copy, $combatant);
                $deaths[$combatant->id] = ($deaths[$combatant->id] ?? 0) + ($combatant->isAlive() ? 0 : $weight);
            }
        }

        return new TurnOutcome($health, $offense, $deaths);
    }

    /**
     * Runs the turn with a fixed list of evasion decisions. Once the list runs out, further
     * rolls are counted (so the caller can branch) and treated as misses.
     *
     * @param array<string, mixed> $snapshot
     * @param array<int, string> $actions
     * @param bool[] $decisions
     * @return array{0: Battle, 1: float, 2: int}
     */
    private function run(Battle $battle, array $snapshot, array $actions, array $decisions): array
    {
        $copy = new Battle(
            $this->snapshot->restore($snapshot, $battle->state->id),
            $battle->registry,
            new FixedRandomSource(),
            new InMemorySpawner(),
        );

        foreach ($copy->combatants() as $combatant) {
            $combatant->automated = true;
            if (isset($actions[$combatant->id])) {
                $combatant->heroId ??= $combatant->id;
                $combatant->automated = false;
                $combatant->queue = [['ability' => $actions[$combatant->id], 'target' => $this->targetFor($copy, $combatant, $actions[$combatant->id])]];
            }
        }

        $probability = 1.0;
        $checks = 0;
        $copy->evasionRoll = function (CombatantState $target, int $evasion) use (&$probability, &$checks, $decisions): bool {
            $chance = max(0.0, min(1.0, $evasion / self::ROLL_RANGE));
            $evaded = $decisions[$checks] ?? false;
            $checks++;
            if ($chance <= 0.0) {
                return false;
            }
            $probability *= $evaded ? $chance : 1 - $chance;

            return $evaded;
        };

        (new TurnResolver())->resolve($copy);

        return [$copy, $probability, $checks];
    }

    private function targetFor(Battle $battle, CombatantState $actor, string $abilityKey): ?int
    {
        $rule = $battle->registry->ability($abilityKey)->targetRule();

        return $rule->needsChosenTarget() && $rule->isHostile() ? (($battle->enemiesOf($actor)[0] ?? null)?->id) : null;
    }

    /**
     * A combatant's damage edge against its opponent after the turn, used to value setup:
     * the damage its next plain attack would deal (attack and Focus against their defense,
     * plus Marked), COUNTER_WEIGHT of the counter it threatens (none while Staggered), and
     * battle-long stat shifts counted over LASTING_TURNS more turns. Terms that hold every
     * turn cancel out, because the Tactician values the change from before the turn.
     */
    public static function offense(Battle $battle, CombatantState $combatant): float
    {
        if (!$combatant->isAlive()) {
            return 0.0;
        }

        $opponent = $battle->enemiesOf($combatant, false)[0] ?? null;
        $attack = $battle->stat($combatant, Stat::Attack);
        $defense = $opponent !== null ? $battle->stat($opponent, Stat::Defense) : 0;

        $power = $attack;
        if ($battle->hasTag($combatant, CombatTag::Focused)) {
            $power += $battle->stat($combatant, Stat::Focus) * max(1, $battle->effects->stacks($combatant, 'focused'));
        }

        $edge = max(0, $power - $defense);
        if ($opponent !== null && $battle->effects->has($opponent, 'marked')) {
            $edge += Marked::BONUS_DAMAGE;
        }
        if (!$battle->hasTag($combatant, CombatTag::Staggered)) {
            $edge += self::COUNTER_WEIGHT * max(0, $attack + $battle->stat($combatant, Stat::Counter) - $defense);
        }

        $lasting = self::statShift($battle, $combatant, Stat::Attack)
            + self::COUNTER_WEIGHT * self::statShift($battle, $combatant, Stat::Counter)
            - ($opponent !== null ? self::statShift($battle, $opponent, Stat::Defense) : 0);

        return (float) $edge + self::LASTING_TURNS * $lasting;
    }

    /**
     * Total battle-long shift (Forged, Analyzed, Outmaneuvered, ...) to one stat.
     */
    private static function statShift(Battle $battle, CombatantState $combatant, Stat $stat): float
    {
        $shift = 0.0;
        foreach ($battle->effects->applicableTo($combatant) as [$effect, $instance]) {
            if (!$effect instanceof AbstractStatShift) {
                continue;
            }
            foreach ($effect->modifiers($instance, $combatant, $battle) as $modifier) {
                if ($modifier->stat === $stat && $modifier->type === ModifierType::Flat) {
                    $shift += $modifier->value;
                }
            }
        }

        return $shift;
    }
}
