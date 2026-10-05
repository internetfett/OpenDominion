<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Discipline: Defend becomes a dodge instead of a block.
 */
class ShadowDance extends AbstractPassive
{
    public function key(): string
    {
        return 'shadow_dance';
    }

    public function name(): string
    {
        return 'Shadow Dance';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Defend also grants +' . ShadowDanceStance::EVASION . ' evasion. Evaded unfocused attacks deal no damage, and evading grants Focus.';
    }

    public function onAbilityDeclared(EffectInstance $instance, ActionContext $context, Battle $battle): void
    {
        if ($context->ability->key() !== 'defend') {
            return;
        }

        $battle->effects->apply($context->actor, 'shadow_dance_stance', 1, 1, [], $context->actor);
    }
}
