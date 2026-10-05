<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class ArcaneWard extends AbstractForm
{
    public function key(): string
    {
        return 'arcane_ward';
    }

    public function name(): string
    {
        return 'Arcane Ward';
    }

    public function opener(): string
    {
        return 'focus';
    }

    public function statusModifiers(): array
    {
        return [Modifier::flat(Stat::Defense, 5)];
    }
}
