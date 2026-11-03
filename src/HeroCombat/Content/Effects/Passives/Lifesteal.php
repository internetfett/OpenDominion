<?php

namespace OpenDominion\HeroCombat\Content\Effects\Passives;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatTag;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Events\MessageKind;

class Lifesteal extends AbstractPassive
{
    public function key(): string
    {
        return 'lifesteal';
    }

    public function name(): string
    {
        return 'Lifesteal';
    }

    public function description(EffectInstance $instance): string
    {
        return 'Attacks heal for 50% of the damage dealt.';
    }

    public function afterDamageDealt(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        $attacker = $damage->attacker();
        if ($attacker === null || $damage->request->isCounter || !$damage->request->hasTag(CombatTag::Attack)) {
            return;
        }

        $amount = (int) round($damage->amount / 2);
        $battle->duringHealing(function () use ($battle, $attacker, $amount) {
            $healed = $battle->heal($attacker, $amount, $attacker, $this->key());
            if ($healed > 0) {
                $battle->say($attacker, "{$attacker->name} heals for {$healed} health.", MessageKind::Detail);
            }
        });
    }
}
