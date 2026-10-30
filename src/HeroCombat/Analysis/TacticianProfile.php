<?php

namespace OpenDominion\HeroCombat\Analysis;

/**
 * How a Tactician values the outcome of a turn. Each personality is a set of weights.
 */
final readonly class TacticianProfile
{
    /**
     * @param float $damageDealt value per point of health the opponent loses
     * @param float $damageTaken cost per point of health you lose
     * @param float $healing value per point of health you regain
     * @param float $setup value per point of change in damage edge (yours minus theirs, see TurnPreview::offense)
     * @param float $victory value of winning (minus the same for losing), scaled by probability
     * @param float $temperature share of the final mix spread evenly over legal actions (0 = pure equilibrium)
     */
    public function __construct(
        public string $key,
        public float $damageDealt = 1.0,
        public float $damageTaken = 1.0,
        public float $healing = 1.0,
        public float $setup = 0.5,
        public float $victory = 200.0,
        public float $temperature = 0.0,
    ) {
    }

    /**
     * @return array<string, self>
     */
    public static function presets(): array
    {
        return [
            'balanced' => new self('balanced'),
            'aggressive' => new self('aggressive', damageDealt: 1.3, damageTaken: 0.8, healing: 0.7, setup: 0.8),
            'defensive' => new self('defensive', damageDealt: 0.8, damageTaken: 1.3, healing: 1.3, setup: 0.4),
        ];
    }

    public function withTemperature(float $temperature): self
    {
        return new self($this->key, $this->damageDealt, $this->damageTaken, $this->healing, $this->setup, $this->victory, $temperature);
    }
}
