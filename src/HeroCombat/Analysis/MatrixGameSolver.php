<?php

namespace OpenDominion\HeroCombat\Analysis;

/**
 * Solves a zero-sum matrix game exactly. The column player's problem is written as the
 * linear program
 *     maximize sum(y)  subject to  A'y <= 1, y >= 0
 * (A' is the payoff matrix shifted to be strictly positive) and solved with the simplex
 * method. The row player's optimal mix is read from the final tableau's dual values.
 * Matrices here are at most 5x5, so this takes microseconds.
 */
final class MatrixGameSolver
{
    private const EPSILON = 1e-9;

    /**
     * @param array<int, array<int, float>> $payoffs payoffs[row][column] to the row player
     * @return array<int, float> the row player's equilibrium probability per row
     */
    public function solve(array $payoffs): array
    {
        return $this->solveWithValue($payoffs)['strategy'];
    }

    /**
     * @param array<int, array<int, float>> $payoffs
     * @return array{strategy: array<int, float>, value: float}
     */
    public function solveWithValue(array $payoffs): array
    {
        $rows = count($payoffs);
        $columns = count($payoffs[0] ?? []);

        if ($rows === 0) {
            return ['strategy' => [], 'value' => 0.0];
        }
        if ($rows === 1 || $columns === 0) {
            return ['strategy' => array_fill(0, $rows, 1 / $rows), 'value' => $columns ? min($payoffs[0]) : 0.0];
        }

        $minimum = min(array_map('min', $payoffs));
        $shift = $minimum <= 0 ? 1 - $minimum : 0.0;

        // Tableau: one row per game row (constraint), one column per game column plus one
        // slack per constraint, plus the right-hand side. The last row is the objective.
        $width = $columns + $rows + 1;
        $tableau = [];
        for ($r = 0; $r < $rows; $r++) {
            $line = array_fill(0, $width, 0.0);
            for ($c = 0; $c < $columns; $c++) {
                $line[$c] = $payoffs[$r][$c] + $shift;
            }
            $line[$columns + $r] = 1.0;
            $line[$width - 1] = 1.0;
            $tableau[$r] = $line;
        }
        $objective = array_fill(0, $width, 0.0);
        for ($c = 0; $c < $columns; $c++) {
            $objective[$c] = -1.0;
        }
        $tableau[$rows] = $objective;

        $this->runSimplex($tableau, $rows, $width);

        $duals = [];
        for ($r = 0; $r < $rows; $r++) {
            $duals[$r] = max(0.0, $tableau[$rows][$columns + $r]);
        }
        $total = array_sum($duals);

        if ($total <= self::EPSILON) {
            return ['strategy' => array_fill(0, $rows, 1 / $rows), 'value' => 0.0];
        }

        return [
            'strategy' => array_map(fn ($dual) => $dual / $total, $duals),
            'value' => 1 / $total - $shift,
        ];
    }

    /**
     * Standard maximization simplex with Bland's rule to avoid cycling.
     *
     * @param array<int, array<int, float>> $tableau modified in place
     */
    private function runSimplex(array &$tableau, int $rows, int $width): void
    {
        $objectiveRow = $rows;

        for ($step = 0; $step < 200; $step++) {
            $pivotColumn = null;
            for ($c = 0; $c < $width - 1; $c++) {
                if ($tableau[$objectiveRow][$c] < -self::EPSILON) {
                    $pivotColumn = $c;
                    break;
                }
            }
            if ($pivotColumn === null) {
                return;
            }

            $pivotRow = null;
            $bestRatio = INF;
            for ($r = 0; $r < $rows; $r++) {
                if ($tableau[$r][$pivotColumn] > self::EPSILON) {
                    $ratio = $tableau[$r][$width - 1] / $tableau[$r][$pivotColumn];
                    if ($ratio < $bestRatio - self::EPSILON) {
                        $bestRatio = $ratio;
                        $pivotRow = $r;
                    }
                }
            }
            if ($pivotRow === null) {
                return;
            }

            $pivot = $tableau[$pivotRow][$pivotColumn];
            for ($c = 0; $c < $width; $c++) {
                $tableau[$pivotRow][$c] /= $pivot;
            }
            for ($r = 0; $r <= $rows; $r++) {
                if ($r === $pivotRow || abs($tableau[$r][$pivotColumn]) <= self::EPSILON) {
                    continue;
                }
                $factor = $tableau[$r][$pivotColumn];
                for ($c = 0; $c < $width; $c++) {
                    $tableau[$r][$c] -= $factor * $tableau[$pivotRow][$c];
                }
            }
        }
    }
}
