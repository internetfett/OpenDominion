<?php

namespace OpenDominion\HeroCombat\Analysis;

use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\InMemorySpawner;
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
     * Damage potential of a combatant's next attack, used to value setup (Focus, attack buffs).
     */
    public static function offense(Battle $battle, CombatantState $combatant): float
    {
        if (!$combatant->isAlive()) {
            return 0.0;
        }

        $offense = $battle->stat($combatant, Stat::Attack);
        if ($battle->hasTag($combatant, CombatTag::Focused)) {
            $offense += $battle->stat($combatant, Stat::Focus) * max(1, $battle->effects->stacks($combatant, 'focused'));
        }

        return (float) $offense;
    }
}
