<?php

namespace OpenDominion\HeroCombat\Content\Loadouts;

/**
 * A kind of combat option a hero can bring to battle.
 */
enum LoadoutSlot: string
{
    case Technique = 'technique';
    case Ability = 'ability';
    case Gear = 'gear';

    /**
     * How many options of this kind a hero can bring.
     */
    public function capacity(): int
    {
        return $this === self::Gear ? 2 : 1;
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
