<?php

namespace OpenDominion\HeroCombat\Engine;

use Closure;

/**
 * Holds the damage and healing of a turn's actions so they land together: health does not
 * change until every action has resolved, and healing counts before anyone falls.
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

    /** @var Closure[] run once the turn has settled */
    private array $settleCallbacks = [];

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
     * Runs a callback after health has settled and deaths are processed.
     */
    public function afterSettle(Closure $callback): void
    {
        $this->settleCallbacks[] = $callback;
    }

    /**
     * @return Closure[]
     */
    public function settleCallbacks(): array
    {
        return $this->settleCallbacks;
    }

    /**
     * @return Closure[]
     */
    public function fallCallbacks(CombatantState $combatant): array
    {
        return $this->fallCallbacks[$combatant->id] ?? [];
    }
}
