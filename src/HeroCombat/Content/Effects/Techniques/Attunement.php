<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Discipline: Focused can be built up twice (see Focused::maxStacks).
 */
class Attunement extends AbstractPassive
{
    public const MAX_FOCUS_STACKS = 2;

    public function key(): string
    {
        return 'attunement';
    }

    public function name(): string
    {
        return 'Attunement';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Focus can be used every turn and stacked twice before your next attack.';
    }

    public function adjustCooldown(EffectInstance $instance, CombatantState $holder, string $abilityKey, int $cooldown): int
    {
        return $abilityKey === 'focus' ? 0 : $cooldown;
    }
}
