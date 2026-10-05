<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Discipline: attacks partly break through a defensive stance.
 */
class Overpower extends AbstractPassive
{
    public const IGNORED_DEFENSE = 5;

    public function key(): string
    {
        return 'overpower';
    }

    public function name(): string
    {
        return 'Overpower';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Your attacks against a defending target ignore ' . self::IGNORED_DEFENSE . ' of their defense.';
    }

    public function beforeDamageDealt(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        $request = $damage->request;
        if ($request->attacker?->id !== $instance->ownerId || $request->isCounter || !$request->hasTag(CombatTag::Attack)) {
            return;
        }

        if ($battle->hasTag($damage->target, CombatTag::Defending)) {
            $request->defendModifier -= self::IGNORED_DEFENSE;
        }
    }
}
