<?php

namespace OpenDominion\Tests\Unit\HeroCombat;

use OpenDominion\HeroCombat\Analysis\MatrixGameSolver;
use PHPUnit\Framework\TestCase;

class MatrixGameSolverTest extends TestCase
{
    public function testRockPaperScissorsIsUniform(): void
    {
        $result = (new MatrixGameSolver())->solveWithValue([[0, -1, 1], [1, 0, -1], [-1, 1, 0]]);

        foreach ($result['strategy'] as $probability) {
            $this->assertEqualsWithDelta(1 / 3, $probability, 1e-9);
        }
        $this->assertEqualsWithDelta(0, $result['value'], 1e-9);
    }

    public function testMixedTwoByTwo(): void
    {
        // Attack/Focus vs Attack/Counter from the design discussion
        $result = (new MatrixGameSolver())->solveWithValue([[0, -20], [-15, 10]]);

        $this->assertEqualsWithDelta(5 / 9, $result['strategy'][0], 1e-9);
        $this->assertEqualsWithDelta(-20 / 3, $result['value'], 1e-9);
    }

    public function testDominatedRowIsNeverPlayed(): void
    {
        $strategy = (new MatrixGameSolver())->solve([[5, 5], [1, 2], [0, 0]]);

        $this->assertEqualsWithDelta(1, $strategy[0], 1e-9);
    }

    public function testRectangularGamesSatisfyDuality(): void
    {
        $solver = new MatrixGameSolver();
        mt_srand(7);

        for ($trial = 0; $trial < 50; $trial++) {
            $rows = mt_rand(2, 5);
            $columns = mt_rand(2, 5);
            $payoffs = [];
            for ($r = 0; $r < $rows; $r++) {
                for ($c = 0; $c < $columns; $c++) {
                    $payoffs[$r][$c] = mt_rand(-50, 50);
                }
            }

            $row = $solver->solveWithValue($payoffs);
            $guarantee = INF;
            for ($c = 0; $c < $columns; $c++) {
                $value = 0;
                for ($r = 0; $r < $rows; $r++) {
                    $value += $row['strategy'][$r] * $payoffs[$r][$c];
                }
                $guarantee = min($guarantee, $value);
            }

            $transposedNegated = [];
            for ($c = 0; $c < $columns; $c++) {
                for ($r = 0; $r < $rows; $r++) {
                    $transposedNegated[$c][$r] = -$payoffs[$r][$c];
                }
            }

            $this->assertEqualsWithDelta($row['value'], $guarantee, 1e-6);
            $this->assertEqualsWithDelta(-$row['value'], $solver->solveWithValue($transposedNegated)['value'], 1e-6);
        }
    }
}
