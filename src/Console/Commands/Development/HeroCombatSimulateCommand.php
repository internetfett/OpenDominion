<?php

namespace OpenDominion\Console\Commands\Development;

use Illuminate\Console\Command;
use InvalidArgumentException;
use OpenDominion\Console\Commands\CommandInterface;
use OpenDominion\HeroCombat\Analysis\BattleSimulator;
use OpenDominion\HeroCombat\Analysis\Build;
use OpenDominion\HeroCombat\Analysis\BuildFactory;
use OpenDominion\HeroCombat\Analysis\SimulationResult;
use OpenDominion\HeroCombat\Analysis\TacticianProfile;

class HeroCombatSimulateCommand extends Command implements CommandInterface
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'hero-combat:simulate
                             {--a= : Build for side A, e.g. "level=9;discipline=riposte;form=feint;gear=greatsword,tower_shield" (default: level 9, no picks)}
                             {--b= : Build for side B (default: level 9, no picks)}
                             {--strategy-a=tactician:balanced : Strategy for side A (tactician:balanced|aggressive|defensive, or balanced, aggressive, defensive, ...)}
                             {--strategy-b=tactician:balanced : Strategy for side B}
                             {--temperature= : Blend Tactician mixes toward uniform play (0-1), applied to every Tactician}
                             {--grid= : Round-robin every option of one kind: disciplines, forms or gear}
                             {--level=9 : Hero level for --grid builds}
                             {--battles=200 : Battles per matchup}
                             {--seed=1 : First battle seed}';

    /** @var string The console command description. */
    protected $description = 'Simulates 1v1 PvP hero battles between builds and strategies (analysis only).';

    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        $builds = resolve(BuildFactory::class);
        $simulator = new BattleSimulator($builds);

        $strategyA = $this->strategy($simulator, (string) $this->option('strategy-a'));
        $strategyB = $this->strategy($simulator, (string) $this->option('strategy-b'));
        $battles = max(1, (int) $this->option('battles'));
        $seed = (int) $this->option('seed');

        try {
            if ($this->option('grid')) {
                $this->grid($simulator, $builds, (string) $this->option('grid'), (int) $this->option('level'), $strategyA, $strategyB, $battles, $seed);
                return;
            }

            $result = $simulator->run(
                $builds->parse((string) ($this->option('a') ?: 'level=9')),
                $builds->parse((string) ($this->option('b') ?: 'level=9')),
                $strategyA,
                $strategyB,
                $battles,
                $seed,
            );
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->report($result);
    }

    private function strategy(BattleSimulator $simulator, string $key): string
    {
        $temperature = $this->option('temperature');
        if ($temperature === null || !str_starts_with($key, 'tactician:')) {
            return $key;
        }

        $profile = TacticianProfile::presets()[substr($key, strlen('tactician:'))] ?? null;
        if ($profile === null) {
            return $key;
        }

        return $simulator->addTactician($profile->withTemperature((float) $temperature));
    }

    private function report(SimulationResult $result): void
    {
        $this->info("A: {$result->labelA} [{$result->strategyA}]");
        $this->info("B: {$result->labelB} [{$result->strategyB}]");
        $this->table(
            ['Battles', 'A wins', 'B wins', 'Draws', 'A score', 'Avg turns'],
            [[
                $result->battles(),
                $result->winsA,
                $result->winsB,
                $result->draws,
                sprintf('%.1f%%', $result->scoreA() * 100),
                sprintf('%.1f', $result->averageTurns()),
            ]],
        );

        foreach (['a' => 'A', 'b' => 'B'] as $side => $label) {
            $shares = array_map(fn ($share, $ability) => sprintf('%s %.0f%%', $ability, $share * 100), $result->actionShares($side), array_keys($result->actionShares($side)));
            $this->line("{$label} actions: " . implode(', ', $shares));
        }
    }

    private function grid(BattleSimulator $simulator, BuildFactory $builds, string $kind, int $level, string $strategyA, string $strategyB, int $battles, int $seed): void
    {
        $type = match ($kind) {
            'disciplines' => 'discipline',
            'forms' => 'form',
            'gear' => 'gear',
            default => throw new InvalidArgumentException("Unknown grid '{$kind}'; use disciplines, forms or gear"),
        };

        $options = $builds->optionsOfType($type, $level);
        $make = fn (string $key) => match ($type) {
            'discipline' => new Build($level, discipline: $key),
            'form' => new Build($level, forms: [$key]),
            'gear' => new Build($level, gear: [$key]),
        };

        $this->info("Row score vs column (wins + half draws), {$battles} battles each with sides swapped halfway, level {$level}, {$strategyA} vs {$strategyB}");

        $rows = [];
        foreach ($options as $rowKey) {
            $row = [$rowKey];
            $total = 0.0;
            foreach ($options as $columnKey) {
                $score = $simulator->balancedScore($make($rowKey), $make($columnKey), $strategyA, $strategyB, $battles, $seed);
                $row[] = sprintf('%.0f%%', $score * 100);
                $total += $score;
            }
            $row[] = sprintf('%.0f%%', $total / count($options) * 100);
            $rows[] = $row;
        }

        $this->table(array_merge([''], $options, ['avg']), $rows);
    }
}
