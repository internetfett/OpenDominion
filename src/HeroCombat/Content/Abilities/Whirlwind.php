<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Targeting\TargetRule;

/**
 * A reduced attack against every enemy. Each hit is a normal attack after the multiplier: it
 * can be evaded, Guarded and Countered, so it pays off against several enemies and little else.
 */
class Whirlwind extends AbstractAttack
{
    public const MULTIPLIER = 0.7;

    public function key(): string
    {
        return 'whirlwind';
    }

    public function name(): string
    {
        return 'Whirlwind';
    }

    public function description(): string
    {
        return 'Strike every enemy for ' . round(self::MULTIPLIER * 100) . '% of normal attack damage.';
    }

    public function rulesNotes(): array
    {
        return [
            'Every enemy you hit can Counter.',
            'Each hit can be evaded separately.',
            'A Guard takes the hit aimed at its ally as well as its own.',
            'Focus and Rally add to every hit.',
            'Can\'t be used two turns in a row.',
        ];
    }

    public function targetRule(): TargetRule
    {
        return TargetRule::AllEnemies;
    }

    public function cooldown(CombatantState $actor, Battle $battle): int
    {
        return 1;
    }

    public function tags(): array
    {
        return [CombatTag::Attack, CombatTag::AreaOfEffect];
    }

    protected function damageOptions(ActionContext $context): array
    {
        return ['multiplier' => self::MULTIPLIER];
    }

    public function messages(): array
    {
        return [
            'hit' => '{actor} whirls into {target} for {damage} damage.',
            'evaded' => '{actor} whirls into {target} for {raw} damage, but {target} evades, reducing damage to {damage}.',
        ];
    }
}
