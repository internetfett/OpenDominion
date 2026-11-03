<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Content\AbstractAbility;
use OpenDominion\HeroCombat\Content\Effects\CaltropsLaid;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Targeting\TargetRule;

/**
 * The Engineer's signature: caltrops around yourself or an ally that wait until an enemy
 * attacks them. Every enemy that strikes them that turn takes the damage, then they're gone.
 */
class Caltrops extends AbstractAbility
{
    public function key(): string
    {
        return 'caltrops';
    }

    public function name(): string
    {
        return 'Caltrops';
    }

    public function description(): string
    {
        return 'Scatter caltrops around you or an ally. They last until an enemy attacks them; each enemy that does that turn takes ' . CaltropsLaid::DAMAGE . ' damage.';
    }

    public function targetRule(): TargetRule
    {
        return TargetRule::SingleAlly;
    }

    public function cooldown(CombatantState $actor, Battle $battle): int
    {
        return 1;
    }

    /**
     * Laid at declare, so they catch this turn's attackers too.
     */
    public function onDeclare(ActionContext $context): void
    {
        $target = $context->intent->targetId === null ? $context->actor : $this->chosenAlly($context);
        if ($target !== null) {
            $context->applyEffect($target, 'caltrops_laid');
        }
    }

    public function resolve(ActionContext $context): void
    {
        $target = $context->target();
        $context->say($target !== null && $target->id !== $context->actor->id ? 'ally' : 'self');
    }

    public function messages(): array
    {
        return [
            'self' => '{actor} scatters caltrops around themselves.',
            'ally' => '{actor} scatters caltrops around {target}.',
        ];
    }
}
