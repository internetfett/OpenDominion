<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Content\AbstractAbility;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Targeting\TargetRule;

/**
 * Doubles your defense this turn. Used on an ally, you also stand guard: hits aimed at
 * them strike you instead.
 */
class Defend extends AbstractAbility
{
    public function key(): string
    {
        return 'defend';
    }

    public function name(): string
    {
        return 'Defend';
    }

    public function description(): string
    {
        return 'Doubles your defense this turn. Used on an ally, attacks aimed at them hit you instead.';
    }

    public function targetRule(): TargetRule
    {
        return TargetRule::SingleAlly;
    }

    public function tags(): array
    {
        return [CombatTag::Stance];
    }

    public function onDeclare(ActionContext $context): void
    {
        $context->applyEffect($context->actor, 'defending', 1);

        $ward = $this->ward($context);
        if ($ward !== null) {
            $context->applyEffect($ward, 'covered', 1);
        }
    }

    public function resolve(ActionContext $context): void
    {
        $target = $context->target();
        if ($target !== null && $target->id !== $context->actor->id) {
            $context->say('guard');
            return;
        }

        $context->say('defend');
    }

    public function messages(): array
    {
        return [
            'defend' => '{actor} takes a defensive stance.',
            'guard' => '{actor} stands guard over {target}.',
        ];
    }

    /**
     * The living ally being guarded, if the defender chose someone other than themselves.
     */
    private function ward(ActionContext $context): ?CombatantState
    {
        $ally = $context->battle->combatant($context->intent->targetId);

        if ($ally === null || $ally->id === $context->actor->id || !$ally->isAlive() || $ally->team !== $context->actor->team) {
            return null;
        }

        return $ally;
    }
}
