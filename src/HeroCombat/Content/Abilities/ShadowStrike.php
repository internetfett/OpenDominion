<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;

/**
 * An attack that can't be evaded or Countered, for 5 less damage: the safe hit when a Counter
 * is likely, and the answer to evasive bosses.
 */
class ShadowStrike extends AbstractAttack
{
    public const DAMAGE_PENALTY = 5;

    public function key(): string
    {
        return 'shadow_strike';
    }

    public function name(): string
    {
        return 'Shadow Strike';
    }

    public function description(): string
    {
        return 'An attack that can\'t be evaded or Countered.';
    }

    public function rulesNotes(): array
    {
        return [
            'Deals ' . self::DAMAGE_PENALTY . ' less damage than a normal attack.',
            'Can\'t be used two turns in a row.',
        ];
    }

    public function cooldown(CombatantState $actor, Battle $battle): int
    {
        return 1;
    }

    protected function damageOptions(ActionContext $context): array
    {
        return ['canEvade' => false, 'canBeCountered' => false, 'bonusDamage' => -self::DAMAGE_PENALTY];
    }

    public function messages(): array
    {
        return ['hit' => '{actor} strikes from the shadows, dealing {damage} damage to {target}.'];
    }
}
