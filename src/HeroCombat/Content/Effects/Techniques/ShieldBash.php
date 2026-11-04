<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Content\Effects\Staggered;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Technique: being attacked while Defending Staggers the attacker (less attack) for the next
 * turns, whether or not the hit does damage. Works for hits taken while guarding an ally.
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
        return 'Being attacked while Defending Staggers the attacker: -' . Staggered::ATTACK . ' attack for ' . Staggered::TURNS . ' turns.';
    }

    public function rulesNotes(): array
    {
        return [
            'Starts the turn after the attack, and also lowers their Counters.',
            'Works even if your Defend blocks all the damage.',
            'Also works when you Guard an ally and take the hit for them.',
            'Being attacked again while they\'re Staggered starts the 2 turns over; it doesn\'t stack.',
        ];
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
        $target = $damage->target;
        $battle->afterActions($attacker, fn () => $battle->effects->apply($attacker, 'staggered', null, 1, [], $target));
    }
}
