<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class Feint extends AbstractCombo
{
    public function key(): string
    {
        return 'feint';
    }

    public function name(): string
    {
        return 'Feint';
    }

    public function opener(): string
    {
        return 'attack';
    }

    public function statusModifiers(): array
    {
        return [Modifier::flat(Stat::Evasion, 10)];
    }
}
