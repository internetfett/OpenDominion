<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Content\AbstractAbility;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Targeting\TargetRule;

class Fortify extends AbstractAbility
{
    public const SHIELD = 20;

    public function key(): string
    {
        return 'fortify';
    }

    public function name(): string
    {
        return 'Fortify';
    }

    public function description(): string
    {
        return 'Prevent the next ' . self::SHIELD . ' non-counter damage dealt to you, starting with this turn\'s attacks.';
    }

    public function targetRule(): TargetRule
    {
        return TargetRule::Self;
    }

    /**
     * The shield goes up at declare, before any attack lands.
     */
    public function onDeclare(ActionContext $context): void
    {
        $context->applyEffect($context->actor, 'shield', null, 1, ['pool' => self::SHIELD]);
    }

    public function cooldown(CombatantState $actor, Battle $battle): int
    {
        return 1;
    }

    public function resolve(ActionContext $context): void
    {
        $context->say('fortify', ['amount' => self::SHIELD]);
    }

    public function messages(): array
    {
        return ['fortify' => '{actor} constructs defenses that will absorb {amount} damage.'];
    }
}
