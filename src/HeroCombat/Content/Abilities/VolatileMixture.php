<?php

namespace OpenDominion\HeroCombat\Content\Abilities;

use OpenDominion\HeroCombat\Engine\ActionContext;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageRequest;
use OpenDominion\HeroCombat\Engine\Events\MessageKind;

class VolatileMixture extends AbstractAttack
{
    public const SUCCESS_CHANCE = 0.8;
    public const DAMAGE_MULTIPLIER = 1.5;

    public function key(): string
    {
        return 'volatile_mixture';
    }

    public function name(): string
    {
        return 'Volatile Mixture';
    }

    public function description(): string
    {
        return 'Attack for ' . round(self::DAMAGE_MULTIPLIER * 100) . '% damage, with a ' . round((1 - self::SUCCESS_CHANCE) * 100) . '% chance to hit yourself instead.';
    }

    public function rulesNotes(): array
    {
        return [
            'A backfire hits you with your own attack against your own defense, Focus included, and the enemy takes nothing.',
            'A backfire can\'t be evaded, and a Guard doesn\'t take it for you.',
            'A Countering target still strikes back after a backfire.',
            'Your Focus is spent either way.',
            'Can\'t be used two turns in a row.',
        ];
    }

    public function cooldown(CombatantState $actor, Battle $battle): int
    {
        return 1;
    }

    public function resolve(ActionContext $context): void
    {
        $target = $context->target();

        if ($context->battle->random->chance(self::SUCCESS_CHANCE)) {
            $result = $context->attack($target, ['multiplier' => self::DAMAGE_MULTIPLIER]);
            static::narrate($context, $result);
            return;
        }

        $backfire = $context->dealDamage(new DamageRequest(
            attacker: $context->actor,
            target: $context->actor,
            abilityKey: $this->key(),
            canEvade: false,
            canBeCountered: false,
            ignoreShield: true,
            tags: [],
            canBeRedirected: false,
        ));
        $context->say('backfire', ['damage' => $backfire->amount]);

        if ($context->battle->hasTag($target, CombatTag::Countering)) {
            $counter = $context->battle->damage->counterAttack($target, $context->actor);
            $context->battle->say($context->actor, "{$target->name} counters the distracted alchemist for {$counter->amount} damage.", MessageKind::Detail);
        }
    }

    public function messages(): array
    {
        return [
            'hit' => '{actor} hurls an unstable concoction, dealing {damage} damage to {target}.',
            'evaded' => '{actor}\'s explosive mixture detonates, but {target} evades most of the blast, taking only {damage} damage.',
            'backfire' => '{actor}\'s volatile mixture explodes prematurely! {actor} is caught in the blast, taking {damage} damage.',
        ];
    }
}
