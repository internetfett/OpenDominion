<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Discipline: evading an attack grants Focus. Focus does not stack, so more evasion means
 * regaining Focus sooner rather than holding more of it.
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
        return 'Evading an attack grants Focus.';
    }

    public function onEvaded(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        $dancer = $this->owner($instance, $battle);
        if ($dancer === null || $damage->target->id !== $dancer->id || $battle->hasTag($dancer, CombatTag::Focused)) {
            return;
        }

        $battle->effects->apply($dancer, 'focused', null, 1, [], $dancer);
        $battle->say($dancer, "{$dancer->name} slips the blow and finds an opening.");
    }
}
