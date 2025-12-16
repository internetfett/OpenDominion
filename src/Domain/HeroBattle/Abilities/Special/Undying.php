<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Special;

use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Models\HeroCombatant;

class Undying extends AbstractAbility
{
    /**
     * Process undying resurrection countdown.
     * When the combatant dies, starts a 5-turn countdown.
     * After 5 turns, resurrects with half health.
     *
     * This should be called during status processing each turn.
     */
    public function processStatus(HeroCombatant $combatant): string
    {
        if ($combatant->current_health > 0) {
            return '';
        }

        $status = $combatant->status ?? [];

        if (!isset($status['undying'])) {
            // Just died - start countdown
            $status['undying'] = $this->config['attributes']['turns'] ?? 5;
            $combatant->update(['status' => $status]);

            return "{$combatant->name} will return from the dead in {$status['undying']} turns.";
        }

        // Countdown is active
        $status['undying'] -= 1;

        if ($status['undying'] == 0) {
            // Resurrection time!
            unset($status['undying']);

            $newHealth = round($combatant->health / 2);
            $combatant->update([
                'current_health' => $newHealth,
                'health' => $newHealth,
                'has_focus' => false,
                'status' => $status
            ]);

            return "{$combatant->name} has returned to life.";
        }

        // Still counting down
        $combatant->update(['status' => $status]);

        return "{$combatant->name} will return from the dead in {$status['undying']} turns.";
    }
}
