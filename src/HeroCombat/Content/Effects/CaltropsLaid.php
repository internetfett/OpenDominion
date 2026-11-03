<?php

namespace OpenDominion\HeroCombat\Content\Effects;

use OpenDominion\HeroCombat\Content\AbstractEffect;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Damage\DamageRequest;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;
use OpenDominion\HeroCombat\Engine\Effects\ExpiryTiming;

/**
 * Caltrops waiting around a combatant. Each enemy attack that strikes the holder answers
 * with flat damage to the attacker once every hit has landed, so every attacker that turn is
 * caught whatever the order. Then the caltrops are gone. The damage ignores defense, evasion
 * and shields, can't be Countered, and a guard can't take it in the attacker's place.
 */
class CaltropsLaid extends AbstractEffect
{
    public const DAMAGE = 15;

    /**
     * Not 'caltrops': loadout keys that are also effect keys are applied as passives when a hero
     * spawns, so the ability and the effect must not share a key.
     */
    public function key(): string
    {
        return 'caltrops_laid';
    }

    public function name(): string
    {
        return 'Caltrops';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Each enemy that attacks this hero takes ' . self::DAMAGE . ' damage, once.';
    }

    public function kind(): EffectKind
    {
        return EffectKind::Buff;
    }

    public function defaultDuration(): ?int
    {
        return null;
    }

    public function expiry(): ExpiryTiming
    {
        return ExpiryTiming::OnConsume;
    }

    public function beforeDamageTaken(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        $request = $damage->request;
        $attacker = $request->attacker;
        $holder = $this->owner($instance, $battle);

        if ($attacker === null || $holder === null || $damage->target !== $holder || $attacker->team === $holder->team
            || $request->isCounter || !$request->hasTag(CombatTag::Attack)) {
            return;
        }

        $battle->afterHits(fn () => $battle->damage->resolve(new DamageRequest(
            attacker: null,
            target: $attacker,
            abilityKey: $this->key(),
            flatDamage: self::DAMAGE,
            canEvade: false,
            canBeCountered: false,
            ignoreShield: true,
            tags: [],
            canBeRedirected: false,
        )));

        if (empty($instance->data['sprung'])) {
            $instance->data['sprung'] = true;
            $battle->spendAfterActions(fn () => $battle->effects->remove($instance));
        }
    }
}
