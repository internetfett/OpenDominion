<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class Spellweave extends AbstractCombo
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
        return 'defend';
    }

    public function statusModifiers(): array
    {
        return [Modifier::flat(Stat::Focus, 10)];
    }
}
