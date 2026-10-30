<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Technique: Recover Invigorates you until the end of the next turn, and the ally you
 * healed as well.
 */
class SecondWind extends AbstractPassive
{
    public function key(): string
    {
        return 'second_wind';
    }

    public function name(): string
    {
        return 'Second Wind';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Recover Invigorates you (+5 attack) until the end of the next turn, and the ally you healed.';
    }

    public function onAbilityUsed(EffectInstance $instance, ActionContext $context, Battle $battle): void
    {
        if ($context->actor->id !== $instance->ownerId || $context->ability->key() !== 'recover') {
            return;
        }

        $battle->effects->apply($context->actor, 'invigorated', null, 1, [], $context->actor);

        $target = $context->target();
        if ($target !== null && $target->id !== $context->actor->id && $target->isAlive()) {
            $battle->effects->apply($target, 'invigorated', null, 1, [], $context->actor);
        }
    }
}
