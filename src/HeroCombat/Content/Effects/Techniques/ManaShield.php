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
 * Discipline: holding Focus also protects. Kept small because Focus lasts until an attack
 * spends it, so the bonus can cover several turns of Defend and Recover.
 */
class ManaShield extends AbstractPassive
{
    public const DEFENSE = 3;

    public function key(): string
    {
        return 'mana_shield';
    }

    public function name(): string
    {
        return 'Mana Shield';
    }

    public function description(EffectInstance $instance): string
    {
        return 'While Focused, +' . self::DEFENSE . ' defense.';
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        if (!$battle->hasTag($subject, CombatTag::Focused)) {
            return [];
        }

        return [Modifier::flat(Stat::Defense, self::DEFENSE)];
    }
}
