<?php

namespace OpenDominion\HeroCombat\Content\Loadouts;

/**
 * Something a hero can equip in a loadout slot. Techniques and abilities share their key with
 * an Effect or Ability, which supplies their behavior, name and description. Gear is plain data.
 */
final readonly class LoadoutOption
{
    /**
     * @param int $level hero level required, or class level for a class signature
     * @param string|null $name required for gear; techniques and abilities use their class's name
     * @param array<string, int> $stats combat stat changes keyed by Stat value
     */
    public function __construct(
        public string $key,
        public LoadoutSlot $slot,
        public int $level,
        public string $icon,
        public ?string $name = null,
        public array $stats = [],
    ) {
    }
}
