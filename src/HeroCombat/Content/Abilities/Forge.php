<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Content\AbstractAbility;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Targeting\TargetRule;

/**
 * A big, lasting boost with a hard limit: two uses per battle, so a hero who turtles can't
 * keep stacking it.
 */
class Forge extends AbstractAbility
{
    public const ATTACK = 4;
    public const MAX_CHARGES = 2;

    public function key(): string
    {
        return 'forge';
    }

    public function name(): string
    {
        return 'Forge';
    }

    public function description(): string
    {
        return '+' . self::ATTACK . ' attack for the rest of the battle.';
    }

    public function rulesNotes(): array
    {
        return [
            'Starts the turn after you use it.',
            self::MAX_CHARGES . ' uses per battle, and they stack.',
            'Also adds to your Counters.',
            'Can\'t be used two turns in a row.',
        ];
    }

    public function maxCharges(CombatantState $actor, Battle $battle): ?int
    {
        return self::MAX_CHARGES;
    }

    public function targetRule(): TargetRule
    {
        return TargetRule::Self;
    }

    public function cooldown(CombatantState $actor, Battle $battle): int
    {
        return 1;
    }

    public function resolve(ActionContext $context): void
    {
        $context->battle->afterActions($context->actor, fn () => $context->applyEffect($context->actor, 'forged'));
        $context->say('forge');
    }

    public function messages(): array
    {
        return ['forge' => '{actor} increases attack value by ' . self::ATTACK . '.'];
    }
}
