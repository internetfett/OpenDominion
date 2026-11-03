<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Content\AbstractAbility;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Targeting\TargetRule;

/**
 * A shield that absorbs the next 20 damage, going up before any attack lands. Enemies only:
 * it was the Architect's Fortify before Fortify became a defense buff.
 */
class StoneShield extends AbstractAbility
{
    public const SHIELD = 20;

    public function key(): string
    {
        return 'stone_shield';
    }

    public function name(): string
    {
        return 'Stone Shield';
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
        return ['fortify' => '{actor} raises a stone shield that will absorb {amount} damage.'];
    }
}
