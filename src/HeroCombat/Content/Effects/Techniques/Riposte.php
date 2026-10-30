<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class Riposte extends AbstractCombo
{
    public function key(): string
    {
        return 'riposte';
    }

    public function name(): string
    {
        return 'Riposte';
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
