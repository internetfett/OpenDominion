<?php

namespace OpenDominion\HeroCombat\Analysis;

use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\BattleEngine;
use OpenDominion\HeroCombat\Engine\BattleState;
use OpenDominion\HeroCombat\Engine\Events\LogEntry;
use OpenDominion\HeroCombat\Engine\InMemorySpawner;
use OpenDominion\HeroCombat\Engine\Random\SeededRandomSource;
use OpenDominion\HeroCombat\Registry\CombatRegistry;
use OpenDominion\Providers\HeroCombatServiceProvider;

/**
 * Plays seeded 1v1 PvP battles between two builds and strategies, in memory.
 * Uses its own registry copy with the Tactician profiles added, so the game's registry
 * never sees them.
 */
class BattleSimulator
{
    public const MAX_TURNS = 150;

    private CombatRegistry $registry;

    public function __construct(private BuildFactory $builds)
    {
        $this->registry = HeroCombatServiceProvider::buildRegistry();
        foreach (TacticianProfile::presets() as $profile) {
            $this->registry->registerStrategy(new TacticianStrategy($profile));
        }
    }

    public function registry(): CombatRegistry
    {
        return $this->registry;
    }

    /**
     * Registers an extra Tactician profile, e.g. one with a temperature for softer play.
     */
    public function addTactician(TacticianProfile $profile): string
    {
        $strategy = new TacticianStrategy($profile);
        $this->registry->registerStrategy($strategy);

        return $strategy->key();
    }

    /**
     * Build A's score against build B with seed luck cancelled out: half the battles are
     * played with the builds on swapped sides over the same seeds, then both halves are
     * averaged.
     */
    public function balancedScore(Build $a, Build $b, string $strategyA, string $strategyB, int $battles, int $seed = 1): float
    {
        $half = max(1, intdiv($battles, 2));

        $forward = $this->run($a, $b, $strategyA, $strategyB, $half, $seed)->scoreA();
        $reverse = $this->run($b, $a, $strategyB, $strategyA, $half, $seed)->scoreA();

        return ($forward + (1 - $reverse)) / 2;
    }

    /**
     * Sides alternate between combatant ids 1 and 2 so neither gets a resolution-order edge.
     */
    public function run(Build $a, Build $b, string $strategyA, string $strategyB, int $battles, int $seed = 1): SimulationResult
    {
        $result = new SimulationResult($a->label(), $b->label(), $strategyA, $strategyB);

        for ($i = 0; $i < $battles; $i++) {
            $battleSeed = $seed + $i;
            $aFirst = $i % 2 === 0;

            $battle = new Battle(
                new BattleState(id: null, turn: 1, seed: $battleSeed, mode: BattleState::MODE_PVP),
                $this->registry,
                SeededRandomSource::forTurn($battleSeed, 1),
                new InMemorySpawner(),
            );
            $idA = $aFirst ? 1 : 2;
            $idB = $aFirst ? 2 : 1;
            $this->builds->addTo($battle, $a, $idA, 1, 'A', $strategyA);
            $this->builds->addTo($battle, $b, $idB, 2, 'B', $strategyB);

            $engine = new BattleEngine();
            while (!$battle->state->finished && $battle->turn() <= self::MAX_TURNS) {
                $engine->advance($battle, function (Battle $battle) use ($battleSeed) {
                    $battle->random = SeededRandomSource::forTurn($battleSeed, $battle->turn());
                });
            }

            foreach ($battle->log->flush() as $entry) {
                if ($entry->action === LogEntry::STATUS) {
                    continue;
                }
                if ($entry->actorId === $idA) {
                    $result->recordAction('a', $entry->action);
                } elseif ($entry->actorId === $idB) {
                    $result->recordAction('b', $entry->action);
                }
            }

            $winner = $battle->state->finished ? $battle->state->winningTeam : null;
            $result->recordBattle(match ($winner) {
                1 => 'a',
                2 => 'b',
                default => 'draw',
            }, $battle->turn());
        }

        return $result;
    }
}
