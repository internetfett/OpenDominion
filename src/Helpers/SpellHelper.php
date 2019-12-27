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

    public function isOffensiveSpell(string $spellKey, Race $race): bool
    {
        return $this->getOffensiveSpells($race)->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }

    public function isInfoOpSpell(string $spellKey): bool
    {
        return $this->getInfoOpSpells()->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }

    public function isHostileSpell(string $spellKey, Race $race): bool
    {
        return $this->getHostileSpells($race)->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }

    public function isBlackOpSpell(string $spellKey): bool
    {
        return $this->getBlackOpSpells($race)->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }

    public function isWarSpell(string $spellKey, Race $race): bool
    {
        return $this->getWarSpells($race)->filter(function ($spell) use ($spellKey) {
            return ($spell['key'] === $spellKey);
        })->isNotEmpty();
    }


    public function getSpells(Race $race): Collection
    {
        return $this->getSelfSpells($race)
            ->merge($this->getOffensiveSpells($race));
    }

    public function getSelfSpells(?Race $race): Collection
    {
        $spells = collect(array_filter([
            [
                'name' => 'Gaia\'s Watch',
                'description' => '+10% food production',
                'key' => 'gaias_watch',
                'mana_cost' => 2,
                'duration' => 12*4,
            ],
            /*
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
            */
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
                'cooldown' => 20,
            ],
            [
                'name' => 'Surreal Perception',
                'description' => 'Reveals the dominion casting offensive spells or committing spy ops against you for 8 hours',
                'key' => 'surreal_perception',
                'mana_cost' => 4,
                'duration' => 8*4,# * $this->militaryCalculator->getWizardRatio($target, 'defense'),
            ],
            [
                'name' => 'Energy Mirror',
                'description' => '20% chance to reflect incoming offensive spells for 8 hours',
                'key' => 'energy_mirror',
                'mana_cost' => 3,
                'duration' => 8*4,
            ]
        ]));

        if($race !== null)
        {
            $racialSpell = $this->getRacialSelfSpell($race);
            $spells->push($racialSpell);
        }

        return $spells;
    }

    public function getRacialSelfSpell(Race $race)
    {
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
                'description' => '+10% offensive power and allows you to kill Undead and Demon units.',
                'key' => 'crusade',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Human', 'Sacred Order', 'Templars']),
            ],
            [
                'name' => 'Miner\'s Sight',
                'description' => '+10% ore and +5% gem production',
                'key' => 'miners_sight',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Dwarf', 'Gnome', 'Artillery']),
            ],
            [
                'name' => 'Killing Rage',
                'description' => '+10% offensive power',
                'key' => 'killing_rage',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Goblin']),
            ],
            [
                'name' => 'Alchemist Flame',
                'description' => '+30 alchemy platinum production',
                'key' => 'alchemist_flame',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Firewalker','Spirit']),
            ],
            [
                'name' => 'Blizzard',
                'description' => '+5% defensive strength',
                'key' => 'blizzard',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Icekin']),
            ],
            [
                'name' => 'Bloodrage',
                'description' => '+10% offensive strength, +10% offensive casualties',
                'key' => 'bloodrage',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Orc', 'Black Orc']),
            ],
            [
                'name' => 'Dragon\'s Roar',
                'description' => 'Enemy draftees do not participate in battle',
                'key' => 'unholy_ghost',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Dragon']),
            ],
            [
                'name' => 'Unholy Ghost',
                'description' => 'Enemy draftees do not participate in battle',
                'key' => 'unholy_ghost',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Dark Elf']),
            ],
            [
                'name' => 'Defensive Frenzy',
                'description' => '+10% defensive strength',
                'key' => 'defensive_frenzy',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Halfling']),
            ],
            [
                'name' => 'Howling',
                'description' => '+10% offensive strength, +10% defensive strength',
                'key' => 'howling',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Kobold']),
            ],
            [
                'name' => 'Warsong',
                'description' => '+10% offensive power',
                'key' => 'warsong',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Wood Elf']),
            ],
            [
                'name' => 'Regeneration',
                'description' => '-25% combat losses',
                'key' => 'regeneration',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Troll', 'Lizardfolk']),
            ],
            [
                'name' => 'Parasitic Hunger',
                'description' => '+50% conversion rate',
                'key' => 'parasitic_hunger',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Lycanthrope', 'Afflicted']),
            ],
            [
                'name' => 'Gaia\'s Blessing',
                'description' => '+20% food production, +10% lumber production',
                'key' => 'gaias_blessing',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Sylvan']),
            ],
            [
                'name' => 'Nightfall',
                'description' => '+5% offensive power',
                'key' => 'nightfall',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Nox']),
            ],
            [
                'name' => 'Campaign',
                'description' => '+25% land generated for successful attack',
                'key' => 'campaign',
                'mana_cost' => 8,
                'duration' => 1*4,
                'races' => collect(['Nomad']),
            ],
            [
                'name' => 'Swarming',
                'description' => 'Double drafting speed (2% instead of 1%)',
                'key' => 'swarming',
                'mana_cost' => 6,
                'duration' => 12*4,
                'races' => collect(['Ants']),
            ],
            [
                'name' => '𒉡𒌋𒆷',
                'description' => 'Void defensive modifiers immune to Temples.',
                'key' => 'voidspell',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Void']),
            ],
            [
                'name' => 'Metabolism',
                'description' => '+200% food production.',
                'key' => 'metabolism',
                'mana_cost' => 8,
                'duration' => 6*4, # 24 ticks / 6 hours
                'cooldown' => 36, # Once every day and a half.
                'races' => collect(['Growth']),
            ],
            [
                'name' => 'Ambush',
                'description' => 'For every 5% Forest, removes 1% of target\'s raw defensive power (max 10% reduction).',
                'key' => 'ambush',
                'mana_cost' => 2,
                'duration' => 1*4,
                'cooldown' => 18, # Once every 18 hours.
                'races' => collect(['Beastfolk']),
            ],
            [
                'name' => 'Coastal Cannons',
                'description' => '+1% Defensive Power for every 1% Water. Max +20%.',
                'key' => 'coastal_cannons',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Armada']),
            ],
            [
                'name' => 'Spiral Architecture',
                'description' => '+10% value for investments into castle improvements performed when active.',
                'key' => 'spiral_architecture',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Imperial Gnome']),
            ],
            [
                'name' => 'Fimbulwinter',
                'description' => '+10% Defensive Power, +15% casualties.',
                'key' => 'fimbulwinter',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Norse']),
            ],
            [
                'name' => 'Desecration',
                'description' => 'Triples enemy draftees casualties.',
                'key' => 'Desecration',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Undead']),
            ],
            [
                'name' => 'Infernal Fury',
                'description' => 'Increases enemy casualties by 20% on successful invasions over 75%.',
                'key' => 'infernal_fury',
                'mana_cost' => 6,
                'duration' => 6*4,
                'races' => collect(['Demon']),
            ],
            [
                'name' => 'Aurora',
                'description' => 'Reduces unit training times by 2 ticks.',
                'key' => 'aurora',
                'mana_cost' => 6,
                'duration' => 6*4, # Half a day
                'races' => collect(['Lux']),
            ],
            [
                'name' => 'Gryphon\'s Call',
                'description' => '4x yeti trapping. Removes offensive power bonus from Gryphon Nests.',
                'key' => 'gryphons_call',
                'mana_cost' => 6,
                'duration' => 1.5*4, # 6 ticks (3 hours)
                'races' => collect(['Snow Elf']),
            ],
            [
                'name' => 'Charybdis\' Gape',
                'description' => 'Increases offensive casualties by 50% against invading forces.',
                'key' => 'charybdis_gape',
                'mana_cost' => 6,
                'duration' => 12*4,
                'races' => collect(['Merfolk']),
            ],
            [
                'name' => 'Portal',
                'description' => 'Must be cast in order to send units on attack. Portal closes quickly and should be used immediately.',
                'key' => 'portal',
                'mana_cost' => 12,
                'duration' => 1,
                'cooldown' => 6, # Every 6 hours.
                'races' => collect(['Dimensionalists']),
            ],
            [
                'name' => 'Call To Arms',
                'description' => 'Training times reduced by 2 for every recent invasion (max -8 ticks).',
                'key' => 'call_to_arms',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Legion II']),
                #'races' => collect(['Legion', 'Legion II', 'Legion III', 'Legion IV', 'Legion V', 'Legion VI']),
            ],
            [
                'name' => 'Underground Caves',
                'description' => 'Psilocybe experience point production replaced by gem production (10x).',
                'key' => 'underground_caves',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Myconid']),
            ],
            [
                'name' => 'Chitin',
                'description' => 'Cocoons receive 1 DP each. Unaffected by Unholy Ghost or Dragon\'s Roar.',
                'key' => 'chitin',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Swarm']),
            ],
            [
                'name' => 'Rainy Season',
                'description' => '+100% defensive power, +100% population growth, +50% food production, +50% lumber production, cannot invade or explore, -50% mining production, no boat production',
                'key' => 'rainy_season',
                'mana_cost' => 12,
                'duration' => 24*4, # Lasts one day
                'cooldown' => 24*7, # Every seven days
                'races' => collect(['Simian']),
            ],
        ]);
    }

    public function getOffensiveSpells(Race $race): Collection
    {
        return $this->getInfoOpSpells()
            ->merge($this->getBlackOpSpells($race))
            ->merge($this->getWarSpells($race));
    }

    public function getInfoOpSpells(): Collection
    {
        return collect([
            [
                'name' => 'Clear Sight',
                'description' => 'Reveal status screen',
                'key' => 'clear_sight',
                'mana_cost' => 0.3,
            ],
            [
                'name' => 'Vision',
                'description' => 'Reveal tech and heroes',
                'key' => 'vision',
                'mana_cost' => 0.5,
            ],
            [
                'name' => 'Revelation',
                'description' => 'Reveal active spells',
                'key' => 'revelation',
                'mana_cost' => 0.75,
            ],
//            [
//                'name' => 'Clairvoyance',
//                'description' => 'Reveal realm town crier',
//                'key' => 'clairvoyance',
//                'mana_cost' => 1.2,
//            ],
//            [
//                'name' => 'Disclosure',
//                'description' => 'Reveal wonder',
//                'key' => 'disclosure',
//                'mana_cost' => 1.2,
//            ],
        ]);
    }

    public function getHostileSpells(?Race $race): Collection
    {
        return $this->getBlackOpSpells($race)
            ->merge($this->getWarSpells($race));
    }

    public function getBlackOpSpells(?Race $race): Collection
    {

      return collect([
          [
              'name' => 'Plague',
              'description' => 'Slows population growth',
              'key' => 'plague',
              'mana_cost' => 3,
              'duration' => 12*2,
          ],
          [
              'name' => 'Insect Swarm',
              'description' => 'Slows food production',
              'key' => 'insect_swarm',
              'mana_cost' => 3,
              'duration' => 12*2,
          ],
          [
              'name' => 'Great Flood',
              'description' => 'Slows boat production',
              'key' => 'great_flood',
              'mana_cost' => 3,
              'duration' => 12*2,
          ],
          [
              'name' => 'Earthquake',
              'description' => 'Slows mine production',
              'key' => 'earthquake',
              'mana_cost' => 3,
              'duration' => 12*2,
          ],
      ]);

      # Commonwealth Academy of Wizardry
      // Lightning and Arcane
      if($race->alignment == 'good')
      {
        return collect([
            [
                'name' => 'Lightning Bolt',
                'description' => 'Destroy the target\'s castle improvements',
                'key' => 'lightning_bolt',
                'mana_cost' => 1,
                'decreases' => [
                    'improvement_markets',
                    'improvement_keep',
                    'improvement_towers',
                    'improvement_forges',
                    'improvement_walls',
                    'improvement_harbor',
                    'improvement_armory',
                    'improvement_infirmary',
                    'improvement_workshops',
                    'improvement_observatory',
                    'improvement_cartography',
                    'improvement_hideouts',
                    'improvement_forestry',
                    'improvement_refinery',
                    'improvement_granaries',
                    'improvement_tissue',
                ],
                'percentage' => 1,
            ],/*
            [
              'name' => 'Silencing',
              'description' => 'Weaken the target\'s wizards',
              'key' => 'silencing',
              'mana_cost' => 1,
              'decreases' => ['wizard_strength'],
              'percentage' => 2,
            ],*/
            [
                'name' => 'Plague',
                'description' => 'Slows population growth',
                'key' => 'plague',
                'mana_cost' => 3,
                'duration' => 12,
            ],
            [
                'name' => 'Insect Swarm',
                'description' => 'Slows food production',
                'key' => 'insect_swarm',
                'mana_cost' => 3,
                'duration' => 12,
            ],
            [
                'name' => 'Great Flood',
                'description' => 'Slows boat production',
                'key' => 'great_flood',
                'mana_cost' => 3,
                'duration' => 12,
            ],
            [
                'name' => 'Earthquake',
                'description' => 'Slows mine production',
                'key' => 'earthquake',
                'mana_cost' => 3,
                'duration' => 12,
            ],
        ]);
      }
      # Imperial Dark Arts Magic
      // Fire and Cold
      elseif($race->alignment == 'evil')
      {
        return collect([
            [
                'name' => 'Fireball',
                'description' => 'Burn target\'s peasants and food',
                'key' => 'fireball',
                'mana_cost' => 1,
                'decreases' => ['peasants', 'resource_food'],
                'percentage' => 1,
            ],
            [
                'name' => 'Iceshard',
                'description' => 'Destroy the target\'s castle improvements',
                'key' => 'lightning_bolt',
                'mana_cost' => 1,
                'decreases' => [
                    'improvement_markets',
                    'improvement_keep',
                    'improvement_towers',
                    'improvement_forges',
                    'improvement_walls',
                    'improvement_harbor',
                    'improvement_armory',
                    'improvement_infirmary',
                    'improvement_workshops',
                    'improvement_observatory',
                    'improvement_cartography',
                    'improvement_hideouts',
                    'improvement_forestry',
                    'improvement_refinery',
                    'improvement_granaries',
                    'improvement_tissue',
                ],
                'percentage' => 1,
            ],
            [
                'name' => 'Plague',
                'description' => 'Slows population growth',
                'key' => 'plague',
                'mana_cost' => 3,
                'duration' => 12,
            ],
            [
                'name' => 'Insect Swarm',
                'description' => 'Slows food production',
                'key' => 'insect_swarm',
                'mana_cost' => 3,
                'duration' => 12,
            ],
            [
                'name' => 'Great Flood',
                'description' => 'Slows boat production',
                'key' => 'great_flood',
                'mana_cost' => 3,
                'duration' => 12,
            ],
            [
                'name' => 'Earthquake',
                'description' => 'Slows mine production',
                'key' => 'earthquake',
                'mana_cost' => 3,
                'duration' => 12,
            ],
        ]);
      }

    }

    public function getWarSpells(?Race $race): Collection
    {
        return collect([
            [
                'name' => 'Lightning Bolt',
                'description' => 'Destroy the target\'s improvements',
                'key' => 'lightning_bolt',
                'mana_cost' => 1,
                'decreases' => [
                    'improvement_markets',
                    'improvement_keep',
                    'improvement_towers',
                    'improvement_forges',
                    'improvement_walls',
                    'improvement_harbor',
                    'improvement_armory',
                    'improvement_infirmary',
                    'improvement_workshops',
                    'improvement_observatory',
                    'improvement_cartography',
                    'improvement_hideouts',
                    'improvement_forestry',
                    'improvement_refinery',
                    'improvement_granaries',
                    'improvement_tissue',
                ],
                'percentage' => 0.75,
            ],
            [
                'name' => 'Fireball',
                'description' => 'Burn target\'s peasants and food',
                'key' => 'fireball',
                'mana_cost' => 1,
                'decreases' => ['peasants', 'resource_food'],
                'percentage' => 0.75,
            ],
            [
                'name' => 'Disband Spies',
                'description' => 'Turns spies into draftees',
                'key' => 'disband_spies',
                'mana_cost' => 1,
                'decreases' => ['military_spies'],
                'increases' => ['military_draftees'],
                'percentage' => 1,
            ],
        ]);
    }
}
