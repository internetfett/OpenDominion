<?php

namespace OpenDominion\HeroCombat\Content\Loadouts;

/**
 * The basic actions every hero brings to battle, and each class's signature: the ability
 * or passive a hero can pick at level 7 once that class reaches UNLOCK_CLASS_LEVEL.
 */
class HeroClassLoadouts
{
    public const BASIC_ABILITIES = ['attack', 'defend', 'focus', 'counter', 'recover'];

    /**
     * Class level (active or not) needed before its signature can be picked.
     */
    public const UNLOCK_CLASS_LEVEL = 5;

    /**
     * Abilities that replace a basic ability when granted.
     *
     * @var array<string, string>
     */
    public const REPLACES = [
        'fortify' => 'defend',
    ];

    /**
     * Each class's signature ability or passive.
     *
     * @var array<string, string>
     */
    public const SIGNATURES = [
        'alchemist' => 'volatile_mixture',
        'architect' => 'fortify',
        'blacksmith' => 'forge',
        'engineer' => 'tactical_awareness',
        'farmer' => 'hardiness',
        'healer' => 'mending',
        'infiltrator' => 'shadow_strike',
        'sorcerer' => 'channeling',
        'scholar' => 'combat_analysis',
        'scion' => 'last_stand',
    ];

    /**
     * The class whose signature this is, or null if any hero may pick it.
     */
    public function classFor(string $key): ?string
    {
        $class = array_search($key, self::SIGNATURES, true);

        return $class === false ? null : $class;
    }

    /**
     * Adds abilities, removing any basic ability they replace.
     *
     * @param string[] $abilities
     * @param string[] $grants
     * @return string[]
     */
    public function withGrants(array $abilities, array $grants): array
    {
        foreach ($grants as $grant) {
            if (isset(self::REPLACES[$grant])) {
                $abilities = array_values(array_filter($abilities, fn ($a) => $a !== self::REPLACES[$grant]));
            }
            if (!in_array($grant, $abilities, true)) {
                $abilities[] = $grant;
            }
        }

        return $abilities;
    }
}
