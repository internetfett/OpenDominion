<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class Ambush extends AbstractForm
{
    public function key(): string
    {
        return 'ambush';
    }

    public function name(): string
    {
        return 'Ambush';
    }

    public function opener(): string
    {
        return 'counter';
    }

    public function statusModifiers(): array
    {
        return [Modifier::flat(Stat::Attack, 5)];
    }
}
