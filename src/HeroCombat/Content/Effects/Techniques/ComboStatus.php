<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractEffect;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;

/**
 * The visible status a Combo grants. Applied while the opener resolves with duration 2, so
 * the turn-end countdown leaves it active for exactly the following turn.
 */
class ComboStatus extends AbstractEffect
{
    public function __construct(private AbstractCombo $combo)
    {
    }

    public function key(): string
    {
        return $this->combo->statusKey();
    }

    public function name(): string
    {
        return $this->combo->name();
    }

    public function description(EffectInstance $instance): string
    {
        return $this->combo->description($instance);
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

        return $this->combo->statusModifiers();
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
