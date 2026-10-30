<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;

/**
 * A Combo: after using its opener ability, the hero gains a status named after the Combo
 * for the next turn. Both sides see the Combo itself from the start, and the status once it fires.
 */
abstract class AbstractCombo extends AbstractPassive
{
    /** Ability key that primes the Combo. */
    abstract public function opener(): string;

    /**
     * Modifiers the status grants for the next turn.
     *
     * @return Modifier[]
     */
    abstract public function statusModifiers(): array;

    public function statusKey(): string
    {
        return $this->key() . '_status';
    }

    /**
     * The status effect definition to register alongside this Combo.
     */
    public function statusEffect(): ComboStatus
    {
        return new ComboStatus($this);
    }

    public function description(EffectInstance $instance): string
    {
        return 'After using ' . ucfirst($this->opener()) . ': ' . ComboStatus::describe($this->statusModifiers()) . ' for the next turn.';
    }

    public function onAbilityUsed(EffectInstance $instance, ActionContext $context, Battle $battle): void
    {
        if ($context->actor->id === $instance->ownerId && $context->ability->key() === $this->opener()) {
            $battle->effects->apply($context->actor, $this->statusKey(), null, 1, [], $context->actor);
        }
    }
}
