<?php

namespace OpenDominion\Domain\HeroBattle\Abilities;

use Illuminate\Support\Collection;
use OpenDominion\Helpers\HeroAbilityHelper;
use OpenDominion\Models\HeroCombatant;

class AbilityRegistry
{
    protected HeroAbilityHelper $abilityHelper;
    protected array $instances = [];

    public function __construct(HeroAbilityHelper $abilityHelper)
    {
        $this->abilityHelper = $abilityHelper;
    }

    /**
     * Get all ability instances for a combatant
     *
     * @return Collection<AbilityInterface>
     */
    public function getAbilitiesForCombatant(HeroCombatant $combatant): Collection
    {
        $abilities = collect();
        $abilityData = $combatant->abilities ?? [];

        // Get saved states from status attribute
        $status = $combatant->status ?? [];
        $savedStates = $status['ability_state'] ?? [];

        foreach ($abilityData as $abilityItem) {
            // Support both string format and object format with config
            if (is_string($abilityItem)) {
                $abilityKey = $abilityItem;
                $overrideConfig = [];
            } elseif (is_array($abilityItem)) {
                $abilityKey = $abilityItem['key'];
                $overrideConfig = $abilityItem['config'] ?? [];
            } else {
                continue;
            }

            $savedState = $savedStates[$abilityKey] ?? [];
            $ability = $this->createAbility($abilityKey, $overrideConfig, $savedState);
            if ($ability) {
                $abilities->push($ability);
            }
        }

        return $abilities;
    }

    /**
     * Create an ability instance with merged config and restored state
     */
    protected function createAbility(string $key, array $overrideConfig = [], array $savedState = []): ?AbilityInterface
    {
        $definition = $this->abilityHelper->getAbilityDefinitions()->get($key);

        if (!$definition || !isset($definition['class'])) {
            return null;
        }

        $class = $definition['class'];
        $defaultConfig = array_merge($definition['config'] ?? [], $definition);
        $config = array_merge($defaultConfig, $overrideConfig);

        $ability = new $class($key, $config);

        // Restore saved state
        if (!empty($savedState)) {
            $ability->restoreState($savedState);
        }

        return $ability;
    }

    /**
     * Save ability states for a combatant
     */
    public function saveAbilityStates(HeroCombatant $combatant, Collection $abilities): void
    {
        $abilityStates = [];

        foreach ($abilities as $ability) {
            $abilityState = $ability->getState();
            if (!empty($abilityState)) {
                $abilityStates[$ability->key] = $abilityState;
            }
        }

        // Save to status attribute
        $status = $combatant->status ?? [];
        $status['ability_state'] = $abilityStates;
        $combatant->status = $status;

        $combatant->save();
    }

    /**
     * Get abilities that implement a specific interface
     */
    public function getAbilitiesByInterface(Collection $abilities, string $interface): Collection
    {
        return $abilities->filter(function ($ability) use ($interface) {
            return $ability instanceof $interface;
        });
    }
}
