<?php

namespace OpenDominion\HeroCombat\Content\Effects;

use OpenDominion\HeroCombat\Engine\Stats\Stat;

/**
 * Applied by the retired Tactical Awareness. Nothing applies it now; it stays registered so
 * battles in progress that already carry it still load.
 */
class Outmaneuvered extends AbstractStatShift
{
    public function key(): string
    {
        return 'outmaneuvered';
    }

    public function name(): string
    {
        return 'Outmaneuvered';
    }

    protected function stat(): Stat
    {
        return Stat::Counter;
    }

    protected function valuePerStack(): int
    {
        return -2;
    }
}
