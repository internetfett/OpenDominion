<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Content\AbstractPassive;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\Damage\DamageContext;

/**
 * Techniques that Mark whoever they hit. The Mark takes hold once the turn's actions are
 * done, so it pays off next turn whichever order the attacks resolved in.
 */
abstract class AbstractMarkingPassive extends AbstractPassive
{
    protected function markAfterActions(DamageContext $damage, Battle $battle): void
    {
        $target = $damage->target;
        $source = $damage->request->attacker;

        $battle->afterActions($target, fn () => $battle->effects->apply($target, 'marked', null, 1, [], $source));
    }
}
