<?php

namespace OpenDominion\HeroCombat\Content\Effects\Passives;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * The Farmer's technique: extra defense at low health, except while Defending or Guarding, so
 * it never stacks with a stance into a wall a Focused attack can't get through.
 */
class Hardiness extends AbstractPassive
{
    public const DEFENSE = 5;
    public const HEALTH = 40;

    public function key(): string
    {
        return 'hardiness';
    }

    public function name(): string
    {
        return 'Hardiness';
    }

    public function description(EffectInstance $instance): string
    {
        return '+' . self::DEFENSE . ' defense at ' . self::HEALTH . ' health or less.';
    }

    public function rulesNotes(): array
    {
        return [
            'Does nothing on turns you Defend or Guard.',
            'Uses your health at the start of the turn, so damage that turn doesn\'t switch it on mid-turn.',
        ];
    }

    public function isActive(EffectInstance $instance, CombatantState $subject, Battle $battle): bool
    {
        return $subject->currentHealth <= self::HEALTH;
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        if ($subject->currentHealth > self::HEALTH || $battle->hasTag($subject, CombatTag::Defending)) {
            return [];
        }

        return [Modifier::flat(Stat::Defense, self::DEFENSE)];
    }
}
