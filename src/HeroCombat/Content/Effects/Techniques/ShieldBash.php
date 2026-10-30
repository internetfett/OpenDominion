<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Technique: being attacked while Defending Staggers the attacker until the end of the next
 * turn, whether or not the hit does damage. Works for hits taken while guarding an ally.
 */
class ShieldBash extends AbstractPassive
{
    public function key(): string
    {
        return 'shield_bash';
    }

    public function name(): string
    {
        return 'Shield Bash';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Being attacked while Defending Staggers the attacker (cannot Counter) until the end of the next turn.';
    }

    public function beforeDamageTaken(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        $request = $damage->request;
        $attacker = $request->attacker;
        if ($damage->target->id !== $instance->ownerId || $attacker === null || $attacker->team === $damage->target->team
            || $request->isCounter || !$request->hasTag(CombatTag::Attack) || !$battle->hasTag($damage->target, CombatTag::Defending)) {
            return;
        }

        if (!$battle->effects->has($attacker, 'staggered')) {
            $battle->say($damage->target, "{$damage->target->name} slams a shield into {$attacker->name}.");
        }
        $battle->effects->apply($attacker, 'staggered', null, 1, [], $damage->target);
    }
}
