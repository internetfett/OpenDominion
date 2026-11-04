<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * A big, lasting debuff with a hard limit: two uses per battle. It weakens the target for
 * everyone, so in 2v2 it pulls the team onto one enemy.
 */
class CombatAnalysis extends AbstractWeakenStat
{
    public const DEFENSE = 4;
    public const MAX_CHARGES = 2;

    public function key(): string
    {
        return 'combat_analysis';
    }

    public function name(): string
    {
        return 'Combat Analysis';
    }

    public function description(): string
    {
        return 'An enemy has −' . self::DEFENSE . ' defense for the rest of the battle.';
    }

    public function rulesNotes(): array
    {
        return [
            'Starts the turn after you use it.',
            self::MAX_CHARGES . ' uses per battle, and they stack, on the same enemy or different ones.',
            'Every attack on that enemy benefits, your ally\'s included.',
            'Has no effect on an enemy with ' . self::STAT_FLOOR . ' defense or less.',
            'Can\'t be used two turns in a row.',
        ];
    }

    public function maxCharges(CombatantState $actor, Battle $battle): ?int
    {
        return self::MAX_CHARGES;
    }

    protected function stat(): Stat
    {
        return Stat::Defense;
    }

    protected function effectKey(): string
    {
        return 'analyzed';
    }

    public function messages(): array
    {
        return parent::messages() + ['weaken' => '{actor} decreases {target}\'s defense value by ' . self::DEFENSE . '.'];
    }
}
