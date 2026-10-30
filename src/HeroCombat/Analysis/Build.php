<?php

namespace OpenDominion\HeroCombat\Analysis;

/**
 * A hero configuration for simulations.
 */
final readonly class Build
{
    /**
     * @param string[] $techniques
     * @param string[] $gear
     */
    public function __construct(
        public int $level = 9,
        public array $techniques = [],
        public ?string $ability = null,
        public array $gear = [],
    ) {
    }

    public function label(): string
    {
        $parts = array_filter([
            implode('+', $this->techniques),
            $this->ability,
            implode('+', $this->gear),
        ]);

        return "L{$this->level}" . ($parts === [] ? ' (basic)' : ' ' . implode(' / ', $parts));
    }
}
