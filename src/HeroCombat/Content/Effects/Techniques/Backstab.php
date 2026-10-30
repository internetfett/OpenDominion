<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Discipline: attacks hit harder against a Focused target. Who is Focused is recorded at the
 * start of the turn, so the bonus does not depend on whether the target's own attack
 * (which spends their Focus) resolves first.
 *
 * Instance data: focused (int[]) opponent ids Focused at the start of this turn.
 */
class Backstab extends AbstractPassive
{
    public const BONUS_DAMAGE = 5;

    public function key(): string
    {
        return 'backstab';
    }

    public function name(): string
    {
        return 'Backstab';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Your attacks deal +' . self::BONUS_DAMAGE . ' damage against a target that was Focused at the start of the turn.';
    }

    public function onTurnStart(EffectInstance $instance, Battle $battle): void
    {
        $owner = $this->owner($instance, $battle);
        if ($owner === null) {
            return;
        }

        $focused = [];
        foreach ($battle->living() as $combatant) {
            if ($combatant->team !== $owner->team && $battle->hasTag($combatant, CombatTag::Focused)) {
                $focused[] = $combatant->id;
            }
        }
        $instance->data['focused'] = $focused;
    }

    public function beforeDamageDealt(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        $request = $damage->request;
        if ($request->attacker?->id !== $instance->ownerId || $request->isCounter || !$request->hasTag(CombatTag::Attack)) {
            return;
        }

        if (in_array($damage->target->id, $instance->data['focused'] ?? [], true)) {
            $request->bonusDamage += self::BONUS_DAMAGE;
        }
    }
}
