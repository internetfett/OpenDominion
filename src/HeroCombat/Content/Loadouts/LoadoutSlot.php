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
     * The most options of this kind a hero can bring.
     */
    public function capacity(): int
    {
        return $this === self::Gear ? 2 : 1;
    }

    /**
     * How many options of this kind a hero of this combat level can bring. The second gear
     * slot opens later than the first.
     */
    public function capacityAt(int $level): int
    {
        return min($this->capacity(), $this->unlockedSlotsAt($level));
    }

    /**
     * The level that opens slot $index (0-based) of this kind, or null when the first slot's
     * opening level comes from its options.
     */
    public function slotLevel(int $index): ?int
    {
        return $this === self::Gear && $index >= 1 ? HeroLoadoutOptions::SECOND_GEAR_LEVEL : null;
    }

    private function unlockedSlotsAt(int $level): int
    {
        $slots = 0;
        for ($index = 0; $index < $this->capacity(); $index++) {
            if ($level >= ($this->slotLevel($index) ?? 0)) {
                $slots++;
            }
        }

        return $slots;
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
