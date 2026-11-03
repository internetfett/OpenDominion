<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Content\AbstractAbility;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Targeting\TargetRule;

/**
 * Doubles your defense this turn; Defending again right after leaves you Exposed. Used on an
 * ally you Guard them instead: 50% more defense, hits aimed at them strike you, and it has
 * no streak penalty.
 */
class Defend extends AbstractAbility
{
    public const GUARD_ACTION = 'guard';

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
        return 'Doubles your defense this turn, but Defending twice in a row leaves you Exposed (-25% defense). Used on an ally, you Guard them: +50% defense, and attacks aimed at them hit you instead.';
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
        $actor = $context->actor;
        $ward = $this->chosenAlly($context);

        if ($ward !== null) {
            $context->applyEffect($actor, 'guarding', 1);
            $context->applyEffect($ward, 'covered', 1);
            return;
        }

        if ($actor->lastAction === $this->key()) {
            $context->applyEffect($actor, 'exposed', 1);
        }

        $context->applyEffect($actor, 'defending', 1);
    }

    public function recordedAction(ActionContext $context): string
    {
        return $this->chosenAlly($context) !== null ? self::GUARD_ACTION : $this->key();
    }

    public function warning(CombatantState $actor): ?string
    {
        return $actor->lastAction === $this->key() ? 'Exposed' : null;
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
}
