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


    public function isBlackOpSpell(string $spellKey, Race $race): bool
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
                'cooldown' => 20,
            ],
            [
                'name' => 'Surreal Perception',
                'description' => 'Shows you the dominion upon receiving offensive spells or spy ops for 8 hours',
                'key' => 'surreal_perception',
                'mana_cost' => 4,
                'duration' => 8*4,# * $this->militaryCalculator->getWizardRatio($target, 'defense'),
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
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Human', 'Sacred Order']),
            ],
            [
                'name' => 'Miner\'s Sight',
                'description' => '+20% ore production (not cumulative with Mining Strength)',
                'key' => 'miners_sight',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Dwarf', 'Gnome']),
            ],
            [
                'name' => 'Killing Rage',
                'description' => '+10% offensive power',
                'key' => 'killing_rage',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Goblin']),
            ],
            [
                'name' => 'Alchemist Flame',
                'description' => '+15 alchemy platinum production',
                'key' => 'alchemist_flame',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Firewalker','Spirit']),
            ],
            /*
            [
                'name' => 'Erosion',
                'description' => '20% of captured land re-zoned into water',
                'key' => 'erosion',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Lizardfolk', 'Merfolk']),
            ],
            */
            [
                'name' => 'Blizzard',
                'description' => '+15% defensive strength (not cumulative with Ares\'s Call)',
                'key' => 'blizzard',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Icekin']),
            ],
            [
                'name' => 'Bloodrage',
                'description' => '+10% offensive strength, +10% offensive casualties',
                'key' => 'bloodrage',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Orc', 'Black Orc', 'Norse']),
            ],
            [
                'name' => 'Unholy Ghost',
                'description' => 'Enemy draftees do not participate in battle',
                'key' => 'unholy_ghost',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Dark Elf', 'Dragon']),
            ],
            [
                'name' => 'Defensive Frenzy',
                'description' => '+20% defensive strength (not cumulative with Ares\'s Call)',
                'key' => 'defensive_frenzy',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Halfling']),
            ],
            [
                'name' => 'Howling',
                'description' => '+10% offensive strength, +10% defensive strength (not cumulative with Ares\'s Call)',
                'key' => 'howling',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Kobold']),
            ],
            [
                'name' => 'Warsong',
                'description' => '+10% offensive power',
                'key' => 'warsong',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Sylvan', 'Templars']),
            ],
            [
                'name' => 'Regeneration',
                'description' => '-25% combat losses',
                'key' => 'regeneration',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Troll', 'Afflicted', 'Lizardfolk']),
            ],
            [
                'name' => 'Parasitic Hunger',
                'description' => '+50% conversion rate',
                'key' => 'parasitic_hunger',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Lycanthrope']),
            ],
            [
                'name' => 'Gaia\'s Blessing',
                'description' => '+20% food production (not cumulative with Gaia\'s Watch), +10% lumber production',
                'key' => 'gaias_blessing',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Wood Elf']),
            ],
            [
                'name' => 'Nightfall',
                'description' => '+5% offensive power',
                'key' => 'nightfall',
                'mana_cost' => 10,
                'duration' => 12*4,
                'races' => collect(['Nox']),
            ],
            [
                'name' => 'Campaign',
                'description' => '+15% land generated for successful attack',
                'key' => 'campaign',
                'mana_cost' => 10,
                'duration' => 1*4,
                'races' => collect(['Nomad']),
            ],
            [
                'name' => 'Swarming',
                'description' => 'Double draft rate (2% of peasants instead of 1%)',
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
                'description' => '-50% food rot.',
                'key' => 'metabolism',
                'mana_cost' => 8,
                'duration' => 12*4,
                'races' => collect(['Growth']),
            ],

            [
                'name' => 'Ambush',
                'description' => 'For every 5% Forest, removes 1% of target\'s raw defensive power (max 10% reduction).',
                'key' => 'ambush',
                'mana_cost' => 2,
                'duration' => 1*4,
                'cooldown' => 24, # Once per day.
                'races' => collect(['Beastfolk']),
            ],

            [
                'name' => 'Coastal Cannons',
                'description' => '+1% Defensive Power for every 1% Water. Max +20%. Not cumulative with Ares Call.',
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
                'description' => 'Not yet implemented.',
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
                'description' => 'Increases offensive casualties by 25% against invading forces.',
                'key' => 'charybdis_gape',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Merfolk']),
            ],
            [
                'name' => 'Discipline',
                'description' => '+10% offensive power, +10% defensive power, +10% ',
                'key' => 'charybdis_gape',
                'mana_cost' => 5,
                'duration' => 12*4,
                'races' => collect(['Merfolk']),
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
                'mana_cost' => 0.75,
            ],
            [
                'name' => 'Clairvoyance',
                'description' => 'Reveal realm town crier',
                'key' => 'clairvoyance',
                'mana_cost' => 0.75,
            ],
//            [
//                'name' => 'Disclosure',
//                'description' => 'Reveal wonder',
//                'key' => 'disclosure',
//                'mana_cost' => 1.2,
//            ],
        ]);
    }

    public function getBlackOpSpells(Race $race): Collection
    {

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
          ],
          [
              'name' => 'Silencing',
              'description' => 'Weaken the target\'s wizards',
              'key' => 'silencing',
              'mana_cost' => 1,
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
          ],
          [
              'name' => 'Iceshard',
              'description' => 'Destroy the target\'s castle improvements',
              'key' => 'lightning_bolt',
              'mana_cost' => 1,
          ],
        ]);
      }


    }

    public function getWarSpells(Race $race): Collection
    {

      return collect([
          //
      ]);

    }
}
