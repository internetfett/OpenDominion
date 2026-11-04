<?php

namespace OpenDominion\HeroCombat\Content\Effects\Passives;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * A Focused basic Attack is a channeled spell: it leaves its target Exposed from the moment
 * it's declared, so the whole turn's attacks on that target hit harder, an ally's included.
 * Other attacks (Blade Flurry, Whirlwind, class abilities) aren't channeled.
 */
class Channeling extends AbstractPassive
{
    public function key(): string
    {
        return 'channeling';
    }

    public function name(): string
    {
        return 'Channeling';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Your Focused Attack leaves its target Exposed.';
    }

    public function rulesNotes(): array
    {
        return [
            'The target is Exposed for the whole turn, so your ally\'s attacks on it hit harder too.',
            'It adds to any other Exposed on the target.',
            'If a Guard takes the hit, the target you aimed at is still the one Exposed.',
            'Only the basic Attack is channeled, not abilities like Blade Flurry or Whirlwind.',
        ];
    }

    public function onAbilityDeclared(EffectInstance $instance, ActionContext $context, Battle $battle): void
    {
        $actor = $context->actor;
        $ability = $context->ability;
        if ($actor->id !== $instance->ownerId || $ability->key() !== 'attack' || !$battle->hasTag($actor, CombatTag::Focused)) {
            return;
        }

        foreach ($battle->targets->resolve($actor, $ability, $context->intent->targetId) as $target) {
            if ($target->isAlive() && $target->team !== $actor->team) {
                $context->applyEffect($target, 'exposed', 1);
            }
        }
    }
}
