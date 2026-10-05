<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Discipline: Counter can be used every turn, but strikes back for less.
 */
class Riposte extends AbstractPassive
{
    public const COUNTER_MULTIPLIER = 1.0;

    public function key(): string
    {
        return 'riposte';
    }

    public function name(): string
    {
        return 'Riposte';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Counter can be used every turn, but counter-attacks deal ' . (int) (self::COUNTER_MULTIPLIER * 100) . '% damage.';
    }

    public function adjustCooldown(EffectInstance $instance, CombatantState $holder, string $abilityKey, int $cooldown): int
    {
        return $abilityKey === 'counter' ? 0 : $cooldown;
    }

    public function beforeDamageDealt(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        if ($damage->request->isCounter && $damage->request->attacker?->id === $instance->ownerId) {
            $damage->request->multiplier *= self::COUNTER_MULTIPLIER;
        }
    }
}
