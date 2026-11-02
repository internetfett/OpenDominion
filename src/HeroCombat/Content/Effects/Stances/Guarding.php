<?php

namespace OpenDominion\HeroCombat\Content\Effects\Stances;

use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * Defending while standing in front of an ally: still a Defending stance, but with less
 * defense than Defending yourself.
 */
class Guarding extends Defending
{
    public const DEFENSE_BONUS = 0.5;

    public function key(): string
    {
        return 'guarding';
    }

    public function name(): string
    {
        return 'Guarding';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Defense increased by 50% this turn while protecting an ally.';
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        return [Modifier::percent(Stat::Defense, self::DEFENSE_BONUS)];
    }
}
