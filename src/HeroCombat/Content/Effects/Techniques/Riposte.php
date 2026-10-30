<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Technique: a Counter that deals damage Marks the attacker until the end of the next turn.
 */
class Riposte extends AbstractPassive
{
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
        return 'Landing a Counter Marks the attacker (attacks that hit deal +5 damage) until the end of the next turn.';
    }

    public function afterDamageDealt(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        if ($damage->request->attacker?->id !== $instance->ownerId || !$damage->request->isCounter || !$damage->target->isAlive()) {
            return;
        }

        $battle->effects->apply($damage->target, 'marked', null, 1, [], $damage->request->attacker);
    }
}
