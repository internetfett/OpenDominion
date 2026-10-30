<?php

namespace OpenDominion\HeroCombat\Content\Effects;

use OpenDominion\HeroCombat\Content\AbstractEffect;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;

/**
 * Off balance: Counter is blocked. Applied mid-turn with duration 2, so it lasts until the
 * end of the following turn.
 */
class Staggered extends AbstractEffect
{
    public function key(): string
    {
        return 'staggered';
    }

    public function name(): string
    {
        return 'Staggered';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Cannot Counter.';
    }

    public function kind(): EffectKind
    {
        return EffectKind::Debuff;
    }

    public function defaultDuration(): ?int
    {
        return 2;
    }

    public function tags(): array
    {
        return [CombatTag::Staggered];
    }
}
