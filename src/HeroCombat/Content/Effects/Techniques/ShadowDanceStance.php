<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\Effects\Stances\AbstractStance;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;
use OpenDominion\HeroCombat\Engine\Effects\EffectInstance;
use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class ShadowDanceStance extends AbstractStance
{
    public const EVASION = 30;

    public function key(): string
    {
        return 'shadow_dance_stance';
    }

    public function name(): string
    {
        return 'Dancing';
    }

    public function description(EffectInstance $instance): string
    {
        return '+' . self::EVASION . ' evasion this turn; evaded unfocused attacks deal no damage, and evading grants Focus.';
    }

    public function modifiers(EffectInstance $instance, CombatantState $subject, Battle $battle): array
    {
        return [Modifier::flat(Stat::Evasion, self::EVASION)];
    }

    public function onEvaded(EffectInstance $instance, DamageContext $damage, Battle $battle): void
    {
        if (!$damage->attackerFocused) {
            $damage->evadeMultiplier = 0;
        }

        $dancer = $this->owner($instance, $battle);
        if ($dancer !== null && !$battle->hasTag($dancer, \OpenDominion\HeroCombat\Engine\CombatTag::Focused)) {
            $battle->effects->apply($dancer, 'focused', null, 1, [], $dancer);
            $battle->say($dancer, "{$dancer->name} slips the blow and finds an opening.");
        }
    }
}
