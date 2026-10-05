<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class Spellweave extends AbstractForm
{
    public function key(): string
    {
        return 'spellweave';
    }

    public function name(): string
    {
        return 'Spellweave';
    }

    public function opener(): string
    {
        return 'focus';
    }

    public function statusModifiers(): array
    {
        return [Modifier::flat(Stat::Counter, 5)];
    }
}
