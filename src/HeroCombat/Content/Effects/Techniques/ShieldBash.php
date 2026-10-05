<?php

namespace OpenDominion\HeroCombat\Content\Effects\Techniques;

use OpenDominion\HeroCombat\Engine\Stats\Modifier;
use OpenDominion\HeroCombat\Engine\Stats\Stat;

class ShieldBash extends AbstractForm
{
    public function key(): string
    {
        return 'shield_bash';
    }

    public function name(): string
    {
        return 'Shield Bash';
    }

    public function opener(): string
    {
        return 'defend';
    }

    public function statusModifiers(): array
    {
        return [Modifier::flat(Stat::Attack, 5)];
    }
}
