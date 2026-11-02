<?php

namespace OpenDominion\HeroCombat\Engine\Damage;

use OpenDominion\HeroCombat\Engine\CombatantState;

/**
 * Mutable state of a hit while it moves through the damage pipeline. Effect hooks
 * read and adjust this.
 */
final class DamageContext
{
    public CombatantState $target;

    /** Damage after defense, before evasion. */
    public int $raw = 0;

    /** Damage that will be (or was) applied. */
    public int $amount = 0;

    public bool $evaded = false;

    public float $evadeMultiplier = 0.5;

    public bool $attackerFocused = false;

    public int $absorbed = 0;

    public bool $savedFromLethal = false;

    /**
     * How the raw damage was reached, for the turn summary.
     *
     * @var array{flat: int|null, attack: int, focus: int, counter_bonus: int, bonus: int, defense: int, defending: bool, multiplier: float, hits: int}|array{}
     */
    public array $breakdown = [];

    public function __construct(public DamageRequest $request)
    {
        $this->target = $request->target;
    }

    public function attacker(): ?CombatantState
    {
        return $this->request->attacker;
    }
}
