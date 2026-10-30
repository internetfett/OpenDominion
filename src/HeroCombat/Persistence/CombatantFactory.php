<?php

namespace OpenDominion\HeroCombat\Persistence;

use OpenDominion\Calculators\Dominion\HeroCalculator;
use OpenDominion\HeroCombat\Content\Loadouts\HeroClassLoadouts;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\Models\Hero;

/**
 * Adds players' heroes to a battle being set up.
 */
class CombatantFactory
{
    public const DEFAULT_TIME_BANK = 2 * 60 * 60;
    public const DEFAULT_STRATEGY = 'balanced';

    /** Upgrade type whose key names an innate combat effect. */
    public const TECHNIQUE_TYPE = 'technique';

    /** Upgrade type whose key names an ability, or an innate effect for passive signatures. */
    public const ABILITY_TYPE = 'ability';

    public function __construct(
        protected HeroCalculator $heroCalculator,
        protected HeroClassLoadouts $loadouts,
    ) {
    }

    /**
     * @param string[] $grantedAbilities extra abilities (e.g. from the encounter)
     * @param int|null $controllerDominionId who pilots the hero; defaults to its owner
     */
    public function addHero(Battle $battle, Hero $hero, int $team, array $grantedAbilities = [], ?int $controllerDominionId = null): CombatantState
    {
        $stats = $this->heroCalculator->getHeroCombatStats($hero);
        $upgradeKeys = $this->upgradeKeys($hero);
        $upgradeAbilities = array_filter($upgradeKeys, fn (string $key) => $battle->registry->hasAbility($key));

        $combatant = new CombatantState(
            id: 0,
            name: $hero->name,
            team: $team,
            baseStats: $stats,
            currentHealth: $stats['health'],
            abilities: $this->loadouts->withGrants(HeroClassLoadouts::BASIC_ABILITIES, array_merge($upgradeAbilities, $grantedAbilities)),
            ai: self::DEFAULT_STRATEGY,
            heroId: $hero->id,
            dominionId: $hero->dominion_id,
        );
        $combatant->timeBank = self::DEFAULT_TIME_BANK;
        $combatant->controllerDominionId = $controllerDominionId ?? $hero->dominion_id;

        $combatant->id = $battle->spawner->spawn($battle->state, $combatant);
        $battle->state->addCombatant($combatant);

        foreach ($upgradeKeys as $key) {
            if ($battle->registry->hasEffect($key)) {
                $battle->effects->apply($combatant, $key);
            }
        }

        return $combatant;
    }

    /**
     * Techniques and class abilities the hero has unlocked. Each key names an ability or an
     * innate effect.
     *
     * @return string[]
     */
    public function upgradeKeys(Hero $hero): array
    {
        return $hero->upgrades
            ->whereIn('type', [self::TECHNIQUE_TYPE, self::ABILITY_TYPE])
            ->pluck('key')
            ->values()
            ->all();
    }

    /**
     * Class keys a hero has (current class plus any classes in its history).
     *
     * @return string[]
     */
    public function heroClasses(Hero $hero): array
    {
        $classes = [$hero->class];
        foreach ($hero->class_data ?? [] as $classEntry) {
            $class = $classEntry['key'] ?? null;
            if ($class !== null && $this->heroCalculator->heroHasClass($hero, $class)) {
                $classes[] = $class;
            }
        }

        return array_values(array_unique(array_filter($classes)));
    }
}
