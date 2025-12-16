<?php

namespace OpenDominion\Domain\HeroBattle\Abilities\Phased;

class TomeOfPower extends AbstractPhasedAbility
{
    /**
     * The Tome of Power cycles through 5 phases, granting different abilities.
     *
     * Phase 1: Base state
     * Phase 2: Grants channeling
     * Phase 3: Grants channeling + lifesteal
     * Phase 4: Grants channeling + lifesteal + power_strike
     * Phase 5: Grants channeling + lifesteal + power_strike + undying
     */
    public function __construct(array $config = [])
    {
        // Define phase abilities if not provided in config
        if (!isset($config['attributes']['phases'])) {
            $config['attributes'] = array_merge($config['attributes'] ?? [], [
                'turns_per_phase' => 4,
                'max_phase' => 5,
                'cycle_phases' => false, // Stays at phase 5
                'phases' => [
                    1 => [
                        'self_abilities' => [],
                        'message' => '%s begins studying the Tome of Power.',
                    ],
                    2 => [
                        'self_abilities' => ['channeling'],
                        'message' => '%s channels arcane energy from the tome.',
                    ],
                    3 => [
                        'self_abilities' => ['channeling', 'lifesteal'],
                        'message' => '%s draws upon the tome\'s life-draining power.',
                    ],
                    4 => [
                        'self_abilities' => ['channeling', 'lifesteal', 'power_strike'],
                        'message' => '%s unleashes devastating power from the tome.',
                    ],
                    5 => [
                        'self_abilities' => ['channeling', 'lifesteal', 'power_strike', 'undying'],
                        'message' => '%s has mastered the Tome of Power and cannot truly die!',
                    ],
                ],
            ]);
        }

        parent::__construct($config);
    }

    protected function getAbilityKey(): string
    {
        return 'tome_of_power';
    }
}
