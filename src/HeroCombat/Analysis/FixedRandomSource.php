<?php

namespace OpenDominion\HeroCombat\Analysis;

use OpenDominion\HeroCombat\Engine\Random\RandomSource;

/**
 * Deterministic randomness for previews: chances succeed, picks take the first option.
 * Evasion is decided separately through Battle::$evasionRoll.
 */
final class FixedRandomSource implements RandomSource
{
    public function int(int $min, int $max): int
    {
        return $max;
    }

    public function chance(float $probability): bool
    {
        return $probability >= 0.5;
    }

    public function pick(array $items): mixed
    {
        return array_values($items)[0];
    }

    public function weighted(array $weights): string
    {
        arsort($weights);

        return (string) array_key_first($weights);
    }
}
