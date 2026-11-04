<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;

/**
 * Two weaker strikes: the whole attack value, Focus and Rally included, is scaled down before
 * defense comes off each hit. Strong against low defense, weak against Defend. A Counter
 * strikes back once per hit.
 */
class BladeFlurry extends AbstractAttack
{
    public const HITS = 2;
    public const POWER_MULTIPLIER = 0.85;

    public function key(): string
    {
        return 'blade_flurry';
    }

    public function name(): string
    {
        return 'Blade Flurry';
    }

    public function description(): string
    {
        return 'Strike an enemy twice with ' . round(self::POWER_MULTIPLIER * 100) . '% of your attack value.';
    }

    public function rulesNotes(): array
    {
        return [
            'Focus and Rally are added before the ' . round(self::POWER_MULTIPLIER * 100) . '% is taken.',
            'Defense is subtracted from each hit, so it\'s weak against Defend.',
            'One evasion roll covers both hits.',
            'A Counter strikes back once for each hit.',
            'Can\'t be used two turns in a row.',
        ];
    }

    public function cooldown(CombatantState $actor, Battle $battle): int
    {
        return 1;
    }

    protected function damageOptions(ActionContext $context): array
    {
        return ['hits' => self::HITS, 'powerMultiplier' => self::POWER_MULTIPLIER];
    }

    public function messages(): array
    {
        return [
            'hit' => '{actor} unleashes a blade flurry, striking ' . self::HITS . ' times for {damage} damage to {target}.',
            'evaded' => '{actor} unleashes a blade flurry, striking ' . self::HITS . ' times for {raw} damage, but {target} evades, reducing damage to {damage}.',
        ];
    }
}
