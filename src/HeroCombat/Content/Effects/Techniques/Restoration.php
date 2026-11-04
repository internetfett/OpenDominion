<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * Discipline: Recover heals most of its value immediately and the rest over the next three
 * turns. Recasting replaces the remaining ticks, so recovering as soon as the cooldown
 * allows wastes the last one. Two healers restoring the same ally in one turn leave the
 * larger tick, whichever resolves last.
 */
class Restoration extends AbstractPassive
{
    public const IMMEDIATE_SHARE = 0.8;
    public const TICK_SHARE = 0.2;
    public const TICKS = 2;

    public function key(): string
    {
        return 'restoration';
    }

    public function name(): string
    {
        return 'Restoration';
    }

    public function description(EffectInstance $instance): string
    {
        return sprintf(
            'Recover heals %d%% immediately and %d%% at the start of each of the next %d turns.',
            self::IMMEDIATE_SHARE * 100,
            self::TICK_SHARE * 100,
            self::TICKS,
        );
    }

    public function rulesNotes(): array
    {
        return [
            'Works when you Recover an ally too; the healing over time goes to them.',
            'Recovering again replaces any healing over time still to come.',
            'Healing over time comes before anyone acts that turn.',
        ];
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        return [Modifier::percent(Stat::Recover, self::IMMEDIATE_SHARE - 1)];
    }

    public function onAbilityUsed(EffectInstance $instance, ActionContext $context, Battle $battle): void
    {
        $target = $context->target();
        if ($context->ability->key() !== 'recover' || $context->actor->id !== $instance->ownerId || $target === null || !$target->isAlive()) {
            return;
        }

        $fullHeal = $battle->stat($context->actor, Stat::Recover) / self::IMMEDIATE_SHARE;
        $amount = (int) round($fullHeal * self::TICK_SHARE);

        $restoring = $battle->effects->find($target, 'restoring');
        if ($restoring !== null && $restoring->appliedTurn === $battle->turn() && ($restoring->data['amount'] ?? 0) >= $amount) {
            return;
        }

        $battle->effects->apply($target, 'restoring', null, 1, ['amount' => $amount], $context->actor);
    }
}
