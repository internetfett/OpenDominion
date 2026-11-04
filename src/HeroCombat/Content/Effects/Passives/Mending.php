<?php

namespace OpenDominion\HeroCombat\Content\Effects\Passives;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * While Focused, Recover and Heal restore the hero's focus value more. The Focus stays for
 * the next attack, so a Focus turn can pay off twice.
 */
class Mending extends AbstractPassive
{
    public function key(): string
    {
        return 'mending';
    }

    public function name(): string
    {
        return 'Mending';
    }

    public function description(EffectInstance $instance): string
    {
        return 'While Focused, Recover and Heal restore your focus value more.';
    }

    public function rulesNotes(): array
    {
        return [
            'You keep the Focus for your next attack.',
            'Works on Heal too, so your ally gets the extra healing.',
            'Recover and Heal still leave you Exposed and use a charge.',
        ];
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        if (!$battle->hasTag($subject, CombatTag::Focused)) {
            return [];
        }

        return [Modifier::flat(Stat::Recover, $subject->baseStat(Stat::Focus))];
    }
}
