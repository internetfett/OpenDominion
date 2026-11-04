<?php

namespace OpenDominion\HeroCombat\Content\Effects;

use OpenDominion\HeroCombat\Content\AbstractEffect;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;
use OpenDominion\HeroCombat\Engine\Effects\StackingRule;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * Wide open: -25% defense. Each application counts separately, so they add up. Applied for
 * one turn by Defend, Recover, Channeling and Hungering Moon.
 */
class Exposed extends AbstractEffect
{
    public const DEFENSE_PERCENT = 0.25;

    public function key(): string
    {
        return 'exposed';
    }

    public function name(): string
    {
        return 'Exposed';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Defense reduced by ' . (int) round(self::DEFENSE_PERCENT * 100) . '%.';
    }

    public function kind(): EffectKind
    {
        return EffectKind::Debuff;
    }

    public function stacking(): StackingRule
    {
        return StackingRule::Independent;
    }

    public function defaultDuration(): ?int
    {
        return 1;
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        return [Modifier::percent(Stat::Defense, -self::DEFENSE_PERCENT)];
    }
}
