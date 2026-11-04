<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;

/**
 * Discipline: evading an attack grants Focus. It lands after the turn's spending, so a
 * Focused attacker who evades is Focused again. It never adds a second stack: not while
 * already Focused, and not on a turn you Focus yourself (which matters under Channeling).
 */
class ShadowDance extends AbstractPassive
{
    public function key(): string
    {
        return 'shadow_dance';
    }

    public function name(): string
    {
        return 'Shadow Dance';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Evading an attack grants Focus.';
    }

    public function rulesNotes(): array
    {
        return [
            'You gain Focus after the turn\'s actions, so it\'s ready next turn.',
            'Evading while you attack still leaves you Focused afterwards.',
            'Does nothing if you\'re already Focused or Focusing that turn. Focus never stacks.',
            'Shadow Strike can\'t be evaded, so it never triggers this.',
        ];
    }

    public function onEvaded(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        $dancer = $this->owner($instance, $battle);
        if ($dancer === null || $damage->target->id !== $dancer->id) {
            return;
        }

        $battle->afterActions($dancer, function () use ($battle, $dancer) {
            if (!$battle->hasTag($dancer, CombatTag::Focused) && !$this->focusesThemselves($dancer, $battle)) {
                $battle->effects->apply($dancer, 'focused', null, 1, [], $dancer);
                $battle->say($dancer, "{$dancer->name} slips the blow and finds an opening.");
            }
        });
    }

    private function focusesThemselves(CombatantState $dancer, Battle $battle): bool
    {
        $intent = $battle->intentOf($dancer);

        return $intent !== null && $intent->abilityKey === 'focus' && in_array($intent->targetId, [null, $dancer->id], true);
    }
}
