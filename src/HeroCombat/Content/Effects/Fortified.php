<?php

namespace OpenDominion\HeroCombat\Content\Effects;

use OpenDominion\HeroCombat\Content\AbstractEffect;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * From Fortify: +10 defense, except on turns the holder Defends or Guards, so it can never
 * help a stance block a Focused attack.
 */
class Fortified extends AbstractEffect
{
    public const DEFENSE = 10;

    public const TURNS = 3;

    public function key(): string
    {
        return 'fortified';
    }

    public function name(): string
    {
        return 'Fortified';
    }

    public function description(EffectInstance $instance): string
    {
        return '+' . self::DEFENSE . ' defense, except while Defending or Guarding.';
    }

    public function kind(): EffectKind
    {
        return EffectKind::Buff;
    }

    public function defaultDuration(): ?int
    {
        return self::TURNS;
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        if ($battle->hasTag($subject, CombatTag::Defending)) {
            return [];
        }

        return [Modifier::flat(Stat::Defense, self::DEFENSE)];
    }
}
