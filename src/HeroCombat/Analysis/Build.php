<?php

namespace OpenDominion\HeroCombat\Analysis;

/**
 * A hero configuration for simulations.
 */
final readonly class Build
{
    /**
     * @param string[] $gear
     */
    public function __construct(
        public int $level = 9,
        public ?string $technique = null,
        public ?string $ability = null,
        public array $gear = [],
    ) {
    }

    public function label(): string
    {
        $parts = array_filter([
            $this->technique,
            $this->ability,
            implode('+', $this->gear),
        ]);

        return "L{$this->level}" . ($parts === [] ? ' (basic)' : ' ' . implode(' / ', $parts));
    }
}
