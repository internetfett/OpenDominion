<?php

namespace OpenDominion\HeroCombat\Analysis;

final class SimulationResult
{
    public int $winsA = 0;

    public int $winsB = 0;

    public int $draws = 0;

    public int $totalTurns = 0;

    /** @var array<string, array<string, int>> side => ability => count */
    public array $actions = ['a' => [], 'b' => []];

    public function __construct(
        public readonly string $labelA,
        public readonly string $labelB,
        public readonly string $strategyA,
        public readonly string $strategyB,
    ) {
    }

    public function recordBattle(string $outcome, int $turns): void
    {
        match ($outcome) {
            'a' => $this->winsA++,
            'b' => $this->winsB++,
            default => $this->draws++,
        };
        $this->totalTurns += $turns;
    }

    public function recordAction(string $side, string $ability): void
    {
        $this->actions[$side][$ability] = ($this->actions[$side][$ability] ?? 0) + 1;
    }

    public function battles(): int
    {
        return $this->winsA + $this->winsB + $this->draws;
    }

    /** Side A's score: wins plus half of draws, as a share of battles. */
    public function scoreA(): float
    {
        return $this->battles() === 0 ? 0.0 : ($this->winsA + $this->draws / 2) / $this->battles();
    }

    public function averageTurns(): float
    {
        return $this->battles() === 0 ? 0.0 : $this->totalTurns / $this->battles();
    }

    /**
     * @return array<string, float> ability => share of that side's actions
     */
    public function actionShares(string $side): array
    {
        $total = array_sum($this->actions[$side]) ?: 1;
        $shares = array_map(fn ($count) => $count / $total, $this->actions[$side]);
        arsort($shares);

        return $shares;
    }
}
