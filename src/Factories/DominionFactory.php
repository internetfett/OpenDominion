<?php

namespace OpenDominion\Factories;

use OpenDominion\Exceptions\GameException;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Pack;
use OpenDominion\Models\Race;
use OpenDominion\Models\Realm;
use OpenDominion\Models\Round;
use OpenDominion\Models\User;

class DominionFactory
{
    /**
     * Creates and returns a new Dominion instance.
     *
     * @param User $user
     * @param Realm $realm
     * @param Race $race
     * @param string $rulerName
     * @param string $dominionName
     * @param Pack|null $pack
     * @return Dominion
     * @throws GameException
     */
    public function create(
        User $user,
        Realm $realm,
        Race $race,
        string $rulerName,
        string $dominionName,
        ?Pack $pack = null
    ): Dominion {
        $this->guardAgainstMultipleDominionsInARound($user, $realm->round);
        $this->guardAgainstMismatchedAlignments($race, $realm, $realm->round);

        // todo: get starting values from config

        $startingBuildings = $this->getStartingBuildings($race);

        $startingLand = $this->getStartingLand(
            $race,
            $this->getStartingBarrenLand($race),
            $startingBuildings
        );

        if((bool)$race->getPerkValue('cannot_construct')) # Race dependent in the future if multiple non-construct races?
        {
            $startingResources['platinum'] = 200000;
            $startingResources['lumber'] = 0;
            $startingResources['food'] = 0;
            $startingResources['gems'] = 0;
            $startingResources['peasants'] = 1000;
            $startingResources['unit2'] = 500;
        }
        else
        {
            $startingResources['platinum'] = 100000;
            $startingResources['lumber'] = 15000;
            $startingResources['food'] = 0;
            $startingResources['gems'] = 0;
            $startingResources['peasants'] = 1300;
            $startingResources['unit2'] = 150;

        }

        return Dominion::create([
            'user_id' => $user->id,
            'round_id' => $realm->round->id,
            'realm_id' => $realm->id,
            'race_id' => $race->id,
            'pack_id' => $pack->id ?? null,

            'ruler_name' => $rulerName,
            'name' => $dominionName,
            'prestige' => 250,

            'peasants' => $startingResources['peasants'],
            'peasants_last_hour' => 0,

            'draft_rate' => 10,
            'morale' => 100,
            'spy_strength' => 100,
            'wizard_strength' => 100,

            'resource_platinum' => $startingResources['platinum'],
            'resource_food' =>  $startingResources['food'],
            'resource_lumber' => $startingResources['lumber'],
            'resource_mana' => 0,
            'resource_ore' => 0,
            'resource_gems' => $startingResources['gems'],
            'resource_tech' => 0,
            'resource_boats' => 0,

            'improvement_science' => 0,
            'improvement_keep' => 0,
            'improvement_towers' => 0,
            'improvement_forges' => 0,
            'improvement_walls' => 0,
            'improvement_harbor' => 0,

            'military_draftees' => 100,
            'military_unit1' => 0,
            'military_unit2' => $startingResources['unit2'],
            'military_unit3' => 0,
            'military_unit4' => 0,
            'military_spies' => 25,
            'military_wizards' => 25,
            'military_archmages' => 0,

            'land_plain' => $startingLand['plain'],
            'land_mountain' => $startingLand['mountain'],
            'land_swamp' => $startingLand['swamp'],
            'land_cavern' => $startingLand['cavern'],
            'land_forest' => $startingLand['forest'],
            'land_hill' => $startingLand['hill'],
            'land_water' => $startingLand['water'],

            'building_home' => $startingBuildings['home'],
            'building_alchemy' => $startingBuildings['alchemy'],
            'building_farm' => $startingBuildings['farm'],
            'building_smithy' => 0,
            'building_masonry' => 0,
            'building_ore_mine' => 0,
            'building_gryphon_nest' => 0,
            'building_tower' => 0,
            'building_wizard_guild' => 0,
            'building_temple' => 0,
            'building_diamond_mine' => 0,
            'building_school' => 0,
            'building_lumberyard' => $startingBuildings['lumberyard'],
            'building_forest_haven' => 0,
            'building_factory' => 0,
            'building_guard_tower' => 0,
            'building_shrine' => 0,
            'building_barracks' => 0,
            'building_dock' => 0,
        ]);
    }

    /**
     * @param User $user
     * @param Round $round
     * @throws GameException
     */
    protected function guardAgainstMultipleDominionsInARound(User $user, Round $round): void
    {
        $dominionCount = Dominion::query()
            ->where([
                'user_id' => $user->id,
                'round_id' => $round->id,
            ])
            ->count();

        if ($dominionCount > 0) {
            throw new GameException('User already has a dominion in this round');
        }
    }

    /**
     * @param Race $race
     * @param Realm $realm
     * @param Round $round
     * @throws GameException
     */
    protected function guardAgainstMismatchedAlignments(Race $race, Realm $realm, Round $round): void
    {
        if (!$round->mixed_alignment && $race->alignment !== $realm->alignment) {
            throw new GameException('Race and realm alignment do not match');
        }
    }

    /**
     * Get amount of barren land a new Dominion starts with.
     *
     * @return array
     */
    protected function getStartingBarrenLand($race): array
    {
        if((bool)$race->getPerkValue('cannot_construct')) # Race dependent in the future if multiple non-construct races?
        {
            return [
                'plain' => 0,
                'mountain' => 250,
                'swamp' => 0,
                'cavern' => 0,
                'forest' => 0,
                'hill' => 0,
                'water' => 0,
            ];
        }
        else
        {      
            return [
                'plain' => 40,
                'mountain' => 20,
                'swamp' => 20,
                'cavern' => 20,
                'forest' => 20,
                'hill' => 20,
                'water' => 20,
            ];
        }
    }

    /**
     * Get amount of buildings a new Dominion starts with.
     *
     * @return array
     */
    protected function getStartingBuildings($race): array
    {
        if((bool)$race->getPerkValue('cannot_construct'))
        {
            return [
                'home' => 0,
                'alchemy' => 0,
                'farm' => 0,
                'lumberyard' => 0,
            ]; 
        }
        else
        {
            return [
                'home' => 10,
                'alchemy' => 30,
                'farm' => 30,
                'lumberyard' => 20,
            ];            
        }
    }

    /**
     * Get amount of total starting land a new Dominion starts with, factoring
     * in both buildings and barren land.
     *
     * @param Race $race
     * @param array $startingBarrenLand
     * @param array $startingBuildings
     * @return array
     */
    protected function getStartingLand(Race $race, array $startingBarrenLand, array $startingBuildings): array
    {
        $startingLand = [
            'plain' => $startingBarrenLand['plain'] + $startingBuildings['alchemy'] + $startingBuildings['farm'],
            'mountain' => $startingBarrenLand['mountain'],
            'swamp' => $startingBarrenLand['swamp'],
            'cavern' => $startingBarrenLand['cavern'],
            'forest' => $startingBarrenLand['forest'] + $startingBuildings['lumberyard'],
            'hill' => $startingBarrenLand['hill'],
            'water' => $startingBarrenLand['water'],
        ];

        $startingLand[$race->home_land_type] += $startingBuildings['home'];

        return $startingLand;
    }
}
