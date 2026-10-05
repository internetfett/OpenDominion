<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractEffect;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;

/**
 * The visible status a Form grants. Applied while the opener resolves with duration 2, so
 * the turn-end countdown leaves it active for exactly the following turn.
 */
class FormStatus extends AbstractEffect
{
    public function __construct(private AbstractForm $form)
    {
    }

    public function key(): string
    {
        return $this->form->statusKey();
    }

    public function name(): string
    {
        return $this->form->name();
    }

    public function description(EffectInstance $instance): string
    {
        return self::describe($this->form->statusModifiers()) . ' this turn.';
    }

    public function kind(): EffectKind
    {
        return EffectKind::Buff;
    }

    public function defaultDuration(): ?int
    {
        return 2;
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        if ($instance->appliedTurn === $battle->turn()) {
            return [];
        }

        return $this->form->statusModifiers();
    }

    /**
     * @param Modifier[] $modifiers
     */
    public static function describe(array $modifiers): string
    {
        return implode(', ', array_map(
            fn (Modifier $modifier) => sprintf('%+d %s', $modifier->value, strtolower($modifier->stat->label())),
            $modifiers,
        ));
    }
}
