<?php

namespace OpenDominion\HeroCombat\Analysis;

/**
 * A hero configuration for simulations.
 */
final readonly class Build
{
    /**
     * @param string[] $forms
     * @param string[] $gear
     */
    public function __construct(
        public int $level = 9,
        public ?string $discipline = null,
        public array $forms = [],
        public array $gear = [],
    ) {
    }

    public function label(): string
    {
        $parts = array_filter([
            $this->discipline,
            implode('+', $this->forms),
            implode('+', $this->gear),
        ]);

        return "L{$this->level}" . ($parts === [] ? ' (basic)' : ' ' . implode(' / ', $parts));
    }
}
