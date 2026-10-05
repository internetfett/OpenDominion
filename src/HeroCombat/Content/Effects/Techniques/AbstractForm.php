<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;

/**
 * A Form: after using its opener ability, the hero gains a status named after the Form
 * for the next turn. The trigger itself is hidden; players see the status.
 */
abstract class AbstractForm extends AbstractPassive
{
    /** Ability key that primes the Form. */
    abstract public function opener(): string;

    /**
     * Modifiers the status grants for the next turn.
     *
     * @return Modifier[]
     */
    abstract public function statusModifiers(): array;

    public function visible(): bool
    {
        return false;
    }

    public function statusKey(): string
    {
        return $this->key() . '_status';
    }

    /**
     * The status effect definition to register alongside this Form.
     */
    public function statusEffect(): FormStatus
    {
        return new FormStatus($this);
    }

    public function description(EffectInstance $instance): string
    {
        return 'After using ' . ucfirst($this->opener()) . ': ' . FormStatus::describe($this->statusModifiers()) . ' for the next turn.';
    }

    public function onAbilityUsed(EffectInstance $instance, ActionContext $context, Battle $battle): void
    {
        if ($context->actor->id === $instance->ownerId && $context->ability->key() === $this->opener()) {
            $battle->effects->apply($context->actor, $this->statusKey(), null, 1, [], $context->actor);
        }
    }
}
