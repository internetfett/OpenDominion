<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractEffect;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;
use OpenDominion\HeroCombat\Engine\Effects\StackingRule;

/**
 * Heals data.amount at the start of each of the three turns after it was applied. Applied
 * mid-turn with duration 4, the turn-end countdown removes it after the third tick.
 * A new application replaces it, dropping any ticks left.
 */
class Rejuvenating extends AbstractEffect
{
    public function key(): string
    {
        return 'rejuvenating';
    }

    public function name(): string
    {
        return 'Rejuvenating';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Heals ' . ($instance->data['amount'] ?? 0) . ' at the start of each turn.';
    }

    public function kind(): EffectKind
    {
        return EffectKind::Buff;
    }

    public function stacking(): StackingRule
    {
        return StackingRule::Replace;
    }

    public function defaultDuration(): ?int
    {
        return Rejuvenation::TICKS + 1;
    }

    public function tags(): array
    {
        return [CombatTag::Heal];
    }

    public function onTurnStart(EffectInstance $instance, Battle $battle): void
    {
        $owner = $this->owner($instance, $battle);
        if ($owner === null) {
            return;
        }

        $healed = $battle->heal($owner, (int) ($instance->data['amount'] ?? 0), $battle->combatant($instance->sourceId));
        if ($healed > 0) {
            $battle->say($owner, "{$owner->name} recovers {$healed} more health.");
        }
    }
}
