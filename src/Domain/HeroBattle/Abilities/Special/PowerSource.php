<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Special;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\TriggersOnDeath;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

class PowerSource extends AbstractAbility implements TriggersOnDeath
{
    /**
     * Triggered when this combatant dies.
     * Severs connection to a specified target, reducing their stats.
     *
     * Config expected:
     * - target_name: Name of the combatant to weaken
     * - stat_reductions: Array of stat => reduction_amount
     */
    public function onDeath(
        HeroCombatant $combatant,
        HeroBattle $battle,
        Collection $livingCombatants
    ): string {
        $status = $combatant->status ?? [];
        $powerSourceConfig = $status['power_source'] ?? null;

        if (!$powerSourceConfig) {
            return '';
        }

        $targetName = $powerSourceConfig['target_name'] ?? null;
        $statReductions = $powerSourceConfig['stat_reductions'] ?? [];

        if (!$targetName || empty($statReductions)) {
            return '';
        }

        $target = $battle->combatants
            ->where('name', $targetName)
            ->first();

        if ($target === null) {
            return '';
        }

        $changes = [];
        foreach ($statReductions as $stat => $reduction) {
            $oldValue = $target->$stat;
            $target->$stat = max(0, $oldValue - $reduction);
            $changes[] = "{$stat} -{$reduction}";
        }
        $target->save();

        if (!empty($changes)) {
            $changesString = implode(', ', $changes);
            return "{$combatant->name} crumbles to dust, severing its connection to {$targetName} ({$changesString})!";
        }

        return '';
    }
}
