<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Technique: a Focused attack that hits without being evaded Marks the target until the end
 * of the next turn.
 */
class Spellbrand extends AbstractPassive
{
    public function key(): string
    {
        return 'spellbrand';
    }

    public function name(): string
    {
        return 'Spellbrand';
    }

    public function description(EffectInstance $instance): string
    {
        return 'A Focused attack that hits Marks the target (attacks that hit deal +5 damage) until the end of the next turn.';
    }

    public function afterDamageDealt(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        $request = $damage->request;
        if ($request->attacker?->id !== $instance->ownerId || $request->isCounter || !$request->hasTag(CombatTag::Attack)
            || !$damage->attackerFocused || $damage->evaded || !$damage->target->isAlive()) {
            return;
        }

        $battle->effects->apply($damage->target, 'marked', null, 1, [], $request->attacker);
    }
}
