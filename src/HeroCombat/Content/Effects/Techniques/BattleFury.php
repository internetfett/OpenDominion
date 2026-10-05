<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * Discipline: each consecutive Attack hits harder and leaves you more exposed.
 *
 * Instance data: streak (int) consecutive Attacks so far.
 */
class BattleFury extends AbstractPassive
{
    public const ATTACK_PER_STACK = 4;
    public const DEFENSE_PER_STACK = 2;
    public const MAX_STACKS = 3;

    public function key(): string
    {
        return 'battle_fury';
    }

    public function name(): string
    {
        return 'Battle Fury';
    }

    public function description(EffectInstance $instance): string
    {
        $streak = $this->streak($instance);
        $base = 'Each consecutive Attack grants +' . self::ATTACK_PER_STACK . ' attack and -' . self::DEFENSE_PER_STACK . ' defense (up to ' . self::MAX_STACKS . ' times).';

        return $streak > 0 ? $base . " Currently {$streak}." : $base;
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        $streak = $this->streak($instance);
        if ($streak === 0) {
            return [];
        }

        return [
            Modifier::flat(Stat::Attack, self::ATTACK_PER_STACK * $streak),
            Modifier::flat(Stat::Defense, -self::DEFENSE_PER_STACK * $streak),
        ];
    }

    public function onAbilityUsed(EffectInstance $instance, ActionContext $context, Battle $battle): void
    {
        if ($context->actor->id !== $instance->ownerId) {
            return;
        }

        $instance->data['streak'] = $context->ability->key() === 'attack'
            ? min(self::MAX_STACKS, $this->streak($instance) + 1)
            : 0;
    }

    private function streak(EffectInstance $instance): int
    {
        return (int) ($instance->data['streak'] ?? 0);
    }
}
