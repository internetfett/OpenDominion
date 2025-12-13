<?php

namespace OpenDominion\Helpers;

use Illuminate\Support\Collection;
use OpenDominion\Domain\HeroBattle\Abilities\Active\PowerStrike;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Channeling;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Elusive;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Lifesteal;
use OpenDominion\Domain\HeroBattle\Abilities\Passive\Mending;
use OpenDominion\Domain\HeroBattle\Abilities\Special\Hardiness;

class HeroAbilityHelper
{
    /**
     * Get all ability definitions
     *
     * @return Collection
     */
    public function getAbilityDefinitions(): Collection
    {
        return collect([
            'lifesteal' => [
                'class' => Lifesteal::class,
                'config' => [
                    'heal_percent' => 0.5,
                ],
                'display_name' => 'Lifesteal',
                'description' => 'Heals for 50% of damage dealt',
                'icon' => 'ra-heart-bottle',
                'type' => 'passive',
            ],
            'elusive' => [
                'class' => Elusive::class,
                'config' => [],
                'display_name' => 'Elusive',
                'description' => 'Cannot be hit without focus',
                'icon' => 'ra-aware',
                'type' => 'passive',
            ],
            'hardiness' => [
                'class' => Hardiness::class,
                'config' => [
                    'charges' => 1,
                ],
                'display_name' => 'Hardiness',
                'description' => 'Survives a lethal blow once',
                'icon' => 'ra-trophy-skull',
                'type' => 'special',
            ],
            'power_strike' => [
                'class' => PowerStrike::class,
                'config' => [
                    'bonus_damage' => 20,
                    'cooldown' => 3,
                ],
                'display_name' => 'Power Strike',
                'description' => 'Deal 20 bonus damage (3 turn cooldown)',
                'icon' => 'ra-large-hammer',
                'type' => 'active',
            ],
            'channeling' => [
                'class' => Channeling::class,
                'config' => [],
                'display_name' => 'Channeling',
                'description' => 'Maintain focus and stack focus bonus',
                'icon' => 'ra-radial-balance',
                'type' => 'passive',
                'class_requirement' => 'sorcerer',
            ],
            'mending' => [
                'class' => Mending::class,
                'config' => [],
                'display_name' => 'Mending',
                'description' => 'Double healing when focused',
                'icon' => 'ra-health',
                'type' => 'passive',
            ],
            'weakened' => [
                'class' => \OpenDominion\Domain\HeroBattle\Abilities\StatusEffect\Weakened::class,
                'config' => [],
                'display_name' => 'Weakened',
                'description' => 'Defense reduced by 15',
                'icon' => 'ra-broken-shield',
                'type' => 'status_effect',
            ],
        ]);
    }

    /**
     * Get an ability definition by key
     */
    public function getAbilityDefinition(string $key): ?array
    {
        return $this->getAbilityDefinitions()->get($key);
    }
}
