<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * Technique: harder to hit on a turn you Attack. Turns are simultaneous, so it guards
 * against attacks resolving that same turn.
 */
class Feint extends AbstractPassive
{
    public const EVASION_BONUS = 10;

    public function key(): string
    {
        return 'feint';
    }

    public function name(): string
    {
        return 'Feint';
    }

    public function description(EffectInstance $instance): string
    {
        return '+' . self::EVASION_BONUS . ' evasion on a turn you Attack.';
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        $intent = $battle->intentOf($subject);
        if ($intent === null || !$battle->registry->hasAbility($intent->abilityKey)) {
            return [];
        }

        if (!in_array(CombatTag::Attack, $battle->registry->ability($intent->abilityKey)->tags(), true)) {
            return [];
        }

        return [Modifier::flat(Stat::Evasion, self::EVASION_BONUS)];
    }
}
