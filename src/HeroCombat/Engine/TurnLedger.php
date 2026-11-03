<?php

namespace OpenDominion\HeroCombat\Engine;

use Closure;

/**
 * Holds a turn's damage and healing so they land together: health does not change until every
 * action has resolved, and healing counts before anyone falls. It also holds the changes that
 * wait for the turn to settle: what actions spent, then what they earned.
 */
final class TurnLedger
{
    /** @var array<int, int> combatant id => damage */
    private array $damage = [];

    /** @var array<int, int> combatant id => healing */
    private array $healing = [];

    /** @var array<int, bool> combatant ids hit by something lethal saves can't stop */
    private array $unsavable = [];

    /** @var array<int, Closure[]> combatant id => callbacks to run if they fall this turn */
    private array $fallCallbacks = [];

    /** @var Closure[] healing held back from the damage step */
    private array $healingCallbacks = [];

    private bool $healingStarted = false;

    /** @var Closure[] run once the turn has settled, before anything is gained */
    private array $spendCallbacks = [];

    /** @var array<int, array{0: CombatantState, 1: Closure}> run after spending, for recipients still standing */
    private array $gainCallbacks = [];

    public function addDamage(CombatantState $target, int $amount, bool $bypassLethalSave = false): void
    {
        $this->damage[$target->id] = ($this->damage[$target->id] ?? 0) + $amount;

        if ($bypassLethalSave) {
            $this->unsavable[$target->id] = true;
        }
    }

    public function addHealing(CombatantState $target, int $amount): void
    {
        $this->healing[$target->id] = ($this->healing[$target->id] ?? 0) + $amount;
    }

    /**
     * Health once the turn settles. It can be zero or below: that is a pending defeat.
     */
    public function projectedHealth(CombatantState $combatant): int
    {
        return $combatant->currentHealth - ($this->damage[$combatant->id] ?? 0) + ($this->healing[$combatant->id] ?? 0);
    }

    public function canBeSaved(CombatantState $combatant): bool
    {
        return !isset($this->unsavable[$combatant->id]);
    }

    /**
     * Runs a callback when the turn settles, only if the combatant actually fell.
     */
    public function whenFalls(CombatantState $combatant, Closure $callback): void
    {
        $this->fallCallbacks[$combatant->id][] = $callback;
    }

    /**
     * @return Closure[]
     */
    public function fallCallbacks(CombatantState $combatant): array
    {
        return $this->fallCallbacks[$combatant->id] ?? [];
    }

    /**
     * Holds healing back until the healing step.
     */
    public function duringHealing(Closure $callback): void
    {
        $this->healingCallbacks[] = $callback;
    }

    public function healingStarted(): bool
    {
        return $this->healingStarted;
    }

    /**
     * Begins the healing step: every action's damage has landed.
     *
     * @return Closure[] the healing held back until now, in the order it was queued
     */
    public function startHealing(): array
    {
        $this->healingStarted = true;
        $callbacks = $this->healingCallbacks;
        $this->healingCallbacks = [];

        return $callbacks;
    }

    public function spendAfterActions(Closure $callback): void
    {
        $this->spendCallbacks[] = $callback;
    }

    public function afterActions(CombatantState $recipient, Closure $callback): void
    {
        $this->gainCallbacks[] = [$recipient, $callback];
    }

    /**
     * @return Closure[]
     */
    public function spendCallbacks(): array
    {
        return $this->spendCallbacks;
    }

    /**
     * @return array<int, array{0: CombatantState, 1: Closure}>
     */
    public function gainCallbacks(): array
    {
        return $this->gainCallbacks;
    }
}
