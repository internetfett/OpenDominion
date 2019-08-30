<?php

namespace OpenDominion\Helpers;

use Illuminate\Support\Collection;
use OpenDominion\Models\Race;

class SpellHelper
{
    public function getSpellInfo(string $spellKey, Race $race): array
    {
        return $this->getSpells($race)->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->first();
    }

    public function isSelfSpell(string $spellKey, Race $race): bool
    {
        return $this->getSelfSpells($race)->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }

    public function isOffensiveSpell(string $spellKey): bool
    {
        return $this->getOffensiveSpells()->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }

    public function isInfoOpSpell(string $spellKey): bool
    {
        return $this->getInfoOpSpells()->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }

    public function isBlackOpSpell(string $spellKey): bool
    {
        return $this->getBlackOpSpells()->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }

    public function isWarSpell(string $spellKey): bool
    {
        return $this->getWarSpells()->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }

    public function getSpells(Race $race): Collection
    {
        return $this->getSelfSpells($race)
            ->merge($this->getOffensiveSpells());
    }

    public function getSelfSpells(Race $race): Collection
    {
        $racialSpell = $this->getRacialSelfSpell($race);

        return collect(array_filter([
            [
                'name' => 'Gaia\'s Watch',
                'description' => '+10% food production',
                'key' => 'gaias_watch',
                'mana_cost' => 2,
                'duration' => 12*4,
            ],
            [
                'name' => 'Ares\' Call',
                'description' => '+10% defensive power',
                'key' => 'ares_call',
                'mana_cost' => 2.5,
                'duration' => 12*4,
            ],
            [
                'name' => 'Midas Touch',
                'description' => '+10% platinum production',
                'key' => 'midas_touch',
                'mana_cost' => 2.5,
                'duration' => 12*4,
            ],
            [
                'name' => 'Mining Strength',
                'description' => '+10% ore production',
                'key' => 'mining_strength',
                'mana_cost' => 2,
                'duration' => 12*4,
            ],
            [
                'name' => 'Harmony',
                'description' => '+50% population growth',
                'key' => 'harmony',
                'mana_cost' => 2.5,
                'duration' => 12*4,
            ],
            [
                'name' => 'Fool\'s Gold',
                'description' => 'Platinum theft protection for 10 hours, 22 hour recharge',
                'key' => 'fools_gold',
                'mana_cost' => 5,
                'duration' => 10*4,
                'cooldown' => 22*4,
            ],
            [
                'name' => 'Surreal Perception',
                'description' => 'Shows you the dominion upon receiving offensive spells or spy ops',
                'key' => 'surreal_perception',
                'mana_cost' => 4,
                'duration' => 8*4,
            ],
//            [
//                'name' => 'Energy Mirror',
//                'description' => '20% chance to reflect incoming spells',
//                'key' => '',
//                'mana_cost' => 3,
//                'duration' => 8,
//            ],
            $racialSpell
        ]));
    }

    public function getRacialSelfSpell(Race $race) {
        $raceName = $race->name;
        return $this->getRacialSelfSpells()->filter(function ($spell) use ($raceName) {
            return $spell['races']->contains($raceName);
        })->first();
    }

    public function getRacialSelfSpells(): Collection
    {
        return collect([
            [
                'name' => 'Crusade',
                'description' => '+5% offensive power and allows you to kill Spirit/Undead',
                'key' => 'crusade',
                'mana_cost' => 4.5,
                'duration' => 12*4,
                'races' => collect(['Human','Dragon','Templars']),
            ],
            [
                'name' => 'Miner\'s Sight',
                'description' => '+20% ore production (not cumulative with Mining Strength)',
                'key' => 'miners_sight',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Dwarf', 'Gnome']),
            ],
            [
                'name' => 'Killing Rage',
                'description' => '+10% offensive power',
                'key' => 'killing_rage',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Goblin', 'Beastfolk']),
            ],
            [
                'name' => 'Alchemist Flame',
                'description' => '+15 alchemy platinum production',
                'key' => 'alchemist_flame',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Firewalker']),
            ],
            [
                'name' => 'Erosion',
                'description' => '20% of captured land re-zoned into water',
                'key' => 'erosion',
                'mana_cost' => 2.5,
                'duration' => 12*4,
                'races' => collect(['Lizardfolk', 'Merfolk', 'Spirit']),
            ],
            [
                'name' => 'Blizzard',
                'description' => '+15% defensive strength (not cumulative with Ares Call)',
                'key' => 'blizzard',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Icekin']),
            ],
            [
                'name' => 'Unholy Ghost',
                'description' => 'Enemy draftees do not participate in battle',
                'key' => 'unholy_ghost',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Dark Elf']),
            ],
            [
                'name' => 'Defensive Frenzy',
                'description' => '+20% defensive strength (not cumulative with Ares Call)',
                'key' => 'defensive_frenzy',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Halfling']),
            ],
            [
                'name' => 'Warsong',
                'description' => '+10% offensive power',
                'key' => 'warsong',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Sylvan']),
            ],
            [
                'name' => 'Regeneration',
                'description' => '-25% combat losses',
                'key' => 'regeneration',
                'mana_cost' => 5,
                'duration' => 12,
                'races' => collect(['Troll']),
            ],
            [
                'name' => 'Parasitic Hunger',
                'description' => '+50% conversion rate',
                'key' => 'parasitic_hunger',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Lycanthrope', 'Undead']),
            ],
            [
                'name' => 'Gaia\'s Blessing',
                'description' => '+20% food production (not cumulative with Gaia\'s Watch), +10% lumber production',
                'key' => 'gaias_blessing',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Wood Elf','Sacred Order']),
            ],
            [
                'name' => 'Nightfall',
                'description' => '+5% offensive power',
                'key' => 'nightfall',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Nox']),
            ],
            [
                'name' => 'Campaign',
                'description' => '+15% land generated for successful attack',
                'key' => 'campaign',
                'mana_cost' => 5,
                'duration' => 1*4,
                'races' => collect(['Nomad']),
            ],
            [
                'name' => 'Swarming',
                'description' => 'Double draft rate (2% of peasants instead of 1%)',
                'key' => 'swarming',
                'mana_cost' => 3,
                'duration' => 12*4,
                'races' => collect(['Ants']),
            ],
            
            [
                'name' => '𒉡𒌋𒆷',
                'description' => 'Void defensive modifiers immune to Temples.',
                'key' => 'voidspell',
                'mana_cost' => 4,
                'duration' => 12*4,
                'races' => collect(['Void']),
            ],
            
        ]);
    }

    public function getOffensiveSpells(): Collection
    {
        return $this->getInfoOpSpells()
            ->merge($this->getBlackOpSpells())
            ->merge($this->getWarSpells());
    }

    public function getInfoOpSpells(): Collection
    {
        return collect([
            [
                'name' => 'Clear Sight',
                'description' => 'Reveal status screen',
                'key' => 'clear_sight',
                'mana_cost' => 0.5,
            ],
//            [
//                'name' => 'Vision',
//                'description' => 'Reveal tech and heroes',
//                'key' => 'vision',
//                'mana_cost' => 0.5,
//            ],
            [
                'name' => 'Revelation',
                'description' => 'Reveal active spells',
                'key' => 'revelation',
                'mana_cost' => 1.2,
            ],
            [
                'name' => 'Clairvoyance',
                'description' => 'Reveal realm town crier',
                'key' => 'clairvoyance',
                'mana_cost' => 1.2,
            ],
//            [
//                'name' => 'Disclosure',
//                'description' => 'Reveal wonder',
//                'key' => 'disclosure',
//                'mana_cost' => 1.2,
//            ],
        ]);
    }

    public function getBlackOpSpells(): Collection
    {
        return collect([
            // plague
            // insect swarm
            // great flood
            // earthquake
        ]);
    }

    public function getWarSpells(): Collection
    {
        return collect([
            // fireball
            // lightning bolt
            // disband spies
        ]);
    }
}
