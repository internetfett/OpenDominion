<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

/**
 * Stat Action Processor
 *
 * Modifies combatant stats (attack, defense, evasion, shield, etc.)
 * Can target self or other combatants.
 */
class StatActionProcessor extends AbstractActionProcessor
{
    public function process(CombatContext $context): void
    {
        $stat = $context->actionDef['attributes']['stat'];
        $value = $context->actionDef['attributes']['value'];

        // No direct damage
        $context->damage = 0;
        $context->healing = 0;

        if ($context->actionDef['type'] == 'self') {
            // Modify attacker's stat
            if ($stat == 'shield' && $context->attacker->shield > 0) {
                // If already shielded, reduce the shield bonus
                $value = $value - $context->attacker->shield;
            }
            $context->attacker->increment($stat, $value);

            $message = $this->getMessage($context, 'stat', $context->attacker->name);
        } else {
            // Modify target's stat
            if ($value < 0 && $context->target->{$stat} <= 5) {
                // Stat reduction has no effect if already at minimum
                $message = "{$context->attacker->name} uses {$context->actionDef['name']}, but it has no effect.";
            } else {
                $context->target->increment($stat, $value);
                $message = $this->getMessage($context, 'stat', $context->attacker->name, $context->target->name);
            }
        }

        $context->addMessage($message);
    }
}
