<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Technique: attacking a Defending target leaves it Exposed until the end of the next turn.
 * Applied after the attack resolves, so the attack itself gets no bonus.
 */
class Sunder extends AbstractPassive
{
    public function key(): string
    {
        return 'sunder';
    }

    public function name(): string
    {
        return 'Sunder';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Attacking a Defending target leaves it Exposed (-5 defense) until the end of the next turn.';
    }

    public function onAbilityUsed(EffectInstance $instance, ActionContext $context, Battle $battle): void
    {
        if ($context->actor->id !== $instance->ownerId || !in_array(CombatTag::Attack, $context->ability->tags(), true)) {
            return;
        }

        foreach ($context->targets as $target) {
            if ($target->isAlive() && $target->team !== $context->actor->team && $battle->hasTag($target, CombatTag::Defending)) {
                $battle->effects->apply($target, 'exposed', 2, 1, [], $context->actor);
                $battle->say($context->actor, "{$context->actor->name} batters through {$target->name}'s guard.");
            }
        }
    }
}
