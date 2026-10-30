<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class Wellspring extends AbstractCombo
{
    public function key(): string
    {
        return 'wellspring';
    }

    public function name(): string
    {
        return 'Wellspring';
    }

    public function opener(): string
    {
        return 'focus';
    }

    public function statusModifiers(): array
    {
        return [Modifier::flat(Stat::Recover, 5)];
    }
}
