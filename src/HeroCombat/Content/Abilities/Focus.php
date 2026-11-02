<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Content\AbstractAbility;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\HeroCombat\Engine\Targeting\TargetRule;

/**
 * Your next attack adds your focus value. Used on an ally, it Rallies them instead: their
 * attack this turn adds your focus value, and only this turn.
 */
class Focus extends AbstractAbility
{
    public function key(): string
    {
        return 'focus';
    }

    public function name(): string
    {
        return 'Focus';
    }

    public function description(): string
    {
        return 'Your next attack adds your focus value to its damage. Used on an ally, adds it to their attack this turn instead.';
    }

    public function targetRule(): TargetRule
    {
        return TargetRule::SingleAlly;
    }

    public function cooldown(CombatantState $actor, Battle $battle): int
    {
        return 1;
    }

    public function blockedByTags(): array
    {
        return [CombatTag::Stunned, CombatTag::Silenced];
    }

    public function canUse(CombatantState $actor, Battle $battle): bool
    {
        return $this->canHoldMoreFocus($actor, $battle) || $battle->alliesOf($actor, includeSelf: false) !== [];
    }

    public function canTarget(CombatantState $actor, CombatantState $target, Battle $battle): bool
    {
        return $target->id !== $actor->id || $this->canHoldMoreFocus($actor, $battle);
    }

    public function onDeclare(ActionContext $context): void
    {
        $ally = $this->chosenAlly($context);
        if ($ally !== null) {
            $context->applyEffect($ally, 'rallied', 1, 1, ['bonus' => $context->battle->stat($context->actor, Stat::Focus)]);
        }
    }

    public function resolve(ActionContext $context): void
    {
        $target = $context->target();
        if ($target !== null && $target->id !== $context->actor->id) {
            $context->say('rally');
            return;
        }

        $context->battle->afterActions(fn () => $context->applyEffect($context->actor, 'focused'));
        $context->say('focus');
    }

    private function canHoldMoreFocus(CombatantState $actor, Battle $battle): bool
    {
        if (!$battle->hasTag($actor, CombatTag::Focused)) {
            return true;
        }

        $maxStacks = $battle->registry->effect('focused')->maxStacks($battle, $actor);

        return $maxStacks === null || $battle->effects->stacks($actor, 'focused') < $maxStacks;
    }

    public function messages(): array
    {
        return [
            'focus' => '{actor} focuses their energy for the next attack.',
            'rally' => '{actor} rallies {target}.',
        ];
    }
}
