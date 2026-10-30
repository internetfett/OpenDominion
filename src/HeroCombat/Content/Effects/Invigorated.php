<?php

namespace OpenDominion\HeroCombat\Content\Effects;

use OpenDominion\HeroCombat\Content\AbstractEffect;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * Granted by Second Wind. Applied mid-turn with duration 2, so it lasts until the end of the
 * following turn.
 */
class Invigorated extends AbstractEffect
{
    public const ATTACK_BONUS = 5;

    public function key(): string
    {
        return 'invigorated';
    }

    public function name(): string
    {
        return 'Invigorated';
    }

    public function description(EffectInstance $instance): string
    {
        return '+' . self::ATTACK_BONUS . ' attack.';
    }

    public function kind(): EffectKind
    {
        return EffectKind::Buff;
    }

    public function defaultDuration(): ?int
    {
        return 2;
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        return [Modifier::flat(Stat::Attack, self::ATTACK_BONUS)];
    }
}
