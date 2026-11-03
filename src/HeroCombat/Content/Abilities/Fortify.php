<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Content\AbstractAbility;
use OpenDominion\HeroCombat\Content\Effects\Fortified;
use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Targeting\TargetRule;

/**
 * The Architect's signature: raise defenses around yourself or an ally. The bonus holds
 * while they act and drops while they Defend or Guard, so it never stacks with a stance.
 * Recasting starts the turns over.
 */
class Fortify extends AbstractAbility
{
    public function key(): string
    {
        return 'fortify';
    }

    public function name(): string
    {
        return 'Fortify';
    }

    public function description(): string
    {
        return 'Gives you or an ally +' . Fortified::DEFENSE . ' defense for ' . Fortified::TURNS . ' turns, starting now. It does nothing on turns they Defend or Guard.';
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
     * Goes up at declare, so it covers this turn's attacks.
     */
    public function onDeclare(ActionContext $context): void
    {
        $target = $context->intent->targetId === null ? $context->actor : $this->chosenAlly($context);
        if ($target !== null) {
            $context->applyEffect($target, 'fortified', Fortified::TURNS);
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
            'self' => '{actor} fortifies their position.',
            'ally' => '{actor} fortifies {target}\'s position.',
        ];
    }
}
