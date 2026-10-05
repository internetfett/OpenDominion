<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class SecondWind extends AbstractForm
{
    public function key(): string
    {
        return 'second_wind';
    }

    public function name(): string
    {
        return 'Second Wind';
    }

    public function opener(): string
    {
        return 'recover';
    }

    public function statusModifiers(): array
    {
        return [Modifier::flat(Stat::Defense, 5)];
    }
}
