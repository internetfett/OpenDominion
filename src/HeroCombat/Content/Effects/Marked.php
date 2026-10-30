<?php

namespace OpenDominion\HeroCombat\Content\Effects;

use OpenDominion\HeroCombat\Content\AbstractEffect;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;
use OpenDominion\HeroCombat\Engine\Effects\Hook;

/**
 * Attacks that get through deal extra damage to the holder, whoever makes them. Applied
 * mid-turn with duration 2, so it lasts until the end of the following turn.
 */
class Marked extends AbstractEffect
{
    public const BONUS_DAMAGE = 5;

    public function key(): string
    {
        return 'marked';
    }

    public function name(): string
    {
        return 'Marked';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Attacks that hit deal +' . self::BONUS_DAMAGE . ' damage.';
    }

    public function kind(): EffectKind
    {
        return EffectKind::Debuff;
    }

    public function defaultDuration(): ?int
    {
        return 2;
    }

    /**
     * Runs before shields so they absorb the bonus too.
     */
    public function handlerPriority(Hook $hook): int
    {
        return 20;
    }

    public function beforeDamageTaken(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        $request = $damage->request;
        if ($request->attacker === null || $request->isCounter || !$request->hasTag(CombatTag::Attack) || $damage->amount <= 0) {
            return;
        }

        $damage->amount += self::BONUS_DAMAGE;
    }
}
