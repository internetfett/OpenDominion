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
 * Off balance: less attack, Counters included. Applied after the turn's actions, so it
 * lasts for the next TURNS turns.
 */
class Staggered extends AbstractEffect
{
    public const ATTACK = 5;
    public const TURNS = 2;

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
        return '-' . self::ATTACK . ' attack.';
    }

    public function kind(): EffectKind
    {
        return EffectKind::Debuff;
    }

    public function defaultDuration(): ?int
    {
        return self::TURNS + 1;
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        return [Modifier::flat(Stat::Attack, -self::ATTACK)];
    }
}
