<?php

namespace OpenDominion\HeroCombat\Content\Effects;

use OpenDominion\HeroCombat\Engine\Stats\Stat;

class Analyzed extends AbstractStatShift
{
    public function key(): string
    {
        return 'analyzed';
    }

    public function name(): string
    {
        return 'Analyzed';
    }

    protected function stat(): Stat
    {
        return Stat::Defense;
    }

    protected function valuePerStack(): int
    {
        return -\OpenDominion\HeroCombat\Content\Abilities\CombatAnalysis::DEFENSE;
    }
}
