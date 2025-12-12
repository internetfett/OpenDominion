# OpenDominion Hero System Overview

## Core Architecture

The hero system is implemented across multiple components with a clear separation of concerns:

**Core Models:**
- `Hero` (src/Models/Hero.php) - Main hero entity with experience, class, and upgrade relationships
- `HeroUpgrade` (src/Models/HeroUpgrade.php) - Individual upgrades/abilities heroes can unlock
- `HeroUpgradePerk` (src/Models/HeroUpgradePerk.php) - Specific effects provided by upgrades
- `HeroHeroUpgrade` - Junction table linking heroes to their unlocked upgrades

**Battle System Models:**
- `HeroBattle` - Individual combat instances between heroes
- `HeroCombatant` - Hero participants in battles with combat stats
- `HeroBattleAction` - Individual actions taken during combat
- `HeroBattleQueue` - Matchmaking queue for PvP battles

**Tournament System Models:**
- `HeroTournament` - Organized tournament events
- `HeroTournamentParticipant` - Hero entries in tournaments
- `HeroTournamentBattle` - Tournament-specific battle instances

## Hero Classes

### Basic Classes (Available from game start)
- **Alchemist** - +0.2% platinum production per level
- **Architect** - -1.2% construction costs per level
- **Blacksmith** - -0.25% military training costs per level
- **Engineer** - +0.75% castle investment bonus per level
- **Farmer** - +1.5% food production per level
- **Healer** - -1% casualties per level
- **Infiltrator** - +2.5% spy power per level
- **Sorcerer** - +2.5% wizard power per level

### Advanced Classes (Unlockable after day 5 with requirements)
- **Scholar** - Requires 7,500 RP, provides +0.1% max population per level, includes special abilities
- **Scion** - Requires 500 prestige, provides -0.25% explore cost per level, includes special abilities

## Experience and Leveling

Heroes gain experience through various game activities and level up based on fixed XP thresholds:

**Experience Levels:** (src/Calculators/Dominion/HeroCalculator.php:203-257)
- Level 1: 100 XP
- Level 2: 300 XP  
- Level 3: 600 XP
- Level 4: 1,000 XP
- Level 5: 1,500 XP
- Level 6: 2,250 XP
- Level 7: 3,000 XP
- Level 8: 3,750 XP
- Level 9: 4,750 XP
- Level 10: 6,000 XP
- Level 11: 7,750 XP
- Level 12: 10,000 XP

**Experience Multipliers:**
- Base: 1x
- Shrines: +5% per shrine percentage (max +50%)
- Racial bonuses: Variable by race
- Wonder bonuses: Variable by wonder effects

## Upgrade System

Heroes can unlock upgrades at even levels (2, 4, 6, etc.) up to level 6. Advanced classes like Scion get special level 0 upgrades.

**Upgrade Types:**
- **Passive** - Permanent stat bonuses
- **Effect** - Apply spell-like effects to the dominion
- **Immediate** - One-time effects (e.g., tech refunds)
- **Directive** - Advanced class special abilities

**Upgrade Categories:**
- Combat stat improvements (health, attack, defense, evasion, focus, counter, recover)
- Magic enhancements (spell damage, cost reduction, identity protection)
- Military bonuses (offense, casualty reduction, wonder attack damage)
- Economic benefits (investment bonuses, production increases)

## Combat System

### Battle Mechanics
Heroes engage in turn-based combat with the following stats:
- **Health** - Hit points (80 + 5 per level base)
- **Attack** - Base damage output (40 base)
- **Defense** - Damage reduction (20 base, doubled when defending)
- **Evasion** - Chance to reduce incoming damage by 50% (10% base)
- **Focus** - Bonus attack damage when focused (10 base)
- **Counter** - Bonus damage on counter attacks (10 base)
- **Recover** - Health restoration amount (20 base)
- **Shield** - Temporary damage absorption that depletes before health

### Combat Action Types

#### Basic Actions
- **Attack** - Deal damage to opponent (HeroBattleService.php:452-524)
  - Can be evaded (50% damage reduction)
  - Can be countered by opponent
  - Consumes focus bonus if active
- **Defend** - Double defense for the turn (HeroBattleService.php:526-533)
- **Focus** - Next attack gains focus bonus damage (HeroBattleService.php:535-551)
  - Can be maintained with "channeling" ability
  - Can be stacked with "channeling" ability
- **Counter** - Deal counter damage if opponent attacks (HeroBattleService.php:553-560)
  - Damage calculated based on counter stat
  - Triggers automatically when opponent uses attack action
- **Recover** - Restore health based on recover stat (HeroBattleService.php:562-575)
  - Enhanced by "mending" ability when focused

#### Advanced Actions
- **Stat Action** - Modify combatant stats (HeroBattleService.php:577-602)
  - Can buff self or debuff opponent
  - Affects stats like attack, defense, evasion, shield
  - Minimum stat floor of 5 for debuffs
- **Volatile Action** - High-risk/high-reward attacks (HeroBattleService.php:604-703)
  - Success: Deal bonus damage (configurable multiplier)
  - Failure: Backfire deals damage to self
  - Can still be evaded or countered
- **Flurry Action** - Multiple rapid attacks (HeroBattleService.php:705-780)
  - Multiple attacks in one turn (configurable count)
  - Each attack deals reduced damage (penalty multiplier)
  - Counters trigger multiple times
- **Summon Action** - Spawn NPC allies into battle (HeroBattleService.php:782-798)
  - Creates new combatants mid-battle
  - Summoned creatures join immediately
  - Used by NPCs like Eternal Guardian

### Battle Types

#### 1. PvP Battles (HeroBattleService.php:51-83)
- **Matchmaking**: Simple queue system matches first two players
- **Rating System**: ELO-style combat rating adjustments (HeroBattleService.php:1090-1114)
- **Win/Loss Tracking**: Updates hero statistics
- **Notifications**: Both players notified at start and end
- **Time Bank**: 2-hour time limit per player (DEFAULT_TIME_BANK = 7200 seconds)
- **Protection**: Cannot battle while under protection

#### 2. Practice Battles (HeroBattleService.php:111-145)
Practice battles allow heroes to fight against pre-configured encounters for testing and training:

**Default Encounter:**
- **Evil Twin** - Clone of your hero with identical stats

**Named Encounters** (from HeroEncounterHelper.php):
- **Rabid Bunny** - Seasonal battle (Round 44)
  - Single powerful enemy with high stats
- **Dragonkin** - Raid: Lair of the Dragon
  - Three dragonkin warriors
- **Gate Warden** - Raid: Ironhold Citadel
  - Single tank-style boss with high counter damage
- **Rebel Corsairs** - Raid: The Island Fortress
  - Three corsairs with "blade_flurry" ability
- **Rebel Admiral** - Raid: The Island Fortress
  - Boss with "blade_flurry" and "enrage" abilities
- **The Fallen Kings** - Raid: The Tomb of Kings
  - Three bosses: Warrior King, Sorcerer King, Betrayer King
  - All have "undying" ability (resurrect after 5 turns)
- **The Eternal Guardian** - Raid: The Tomb of Kings
  - Summoner boss with "summon_skeleton" ability
  - Spawns skeleton warriors periodically
- **The Nightbringer** - Raid: Rise of the Nightbringer
  - Boss with "elusive" and "darkness" abilities
  - Two Nox Cultists with "dying_light" (explode to expose boss)
- **The Lich King** - Raid: The Lich King's Fury
  - Final boss with "enrage" ability
  - Protected by Tome of Power (linked via "power_source")

**Practice Battle Features:**
- Non-rated (doesn't affect combat rating)
- No win/loss statistics
- Instant matchmaking (no queue)
- Cannot start while under protection
- Cannot have multiple battles in progress

#### 3. Tournament Battles
- Organized competitive events
- Bracket-style progression
- Separate win/loss tracking for tournaments

### Time Bank System (HeroBattleService.php:231-243)
- **Initial Time**: 2 hours (7200 seconds) per player
- **Depletion**: Time decreases while waiting for player actions
- **Automation**: When time expires, hero switches to automated strategy
- **Display**: Shown as hours and minutes remaining in UI

### Automated Combat Strategies
When automated (or time runs out), heroes follow weighted action selection:
- **Balanced** - Mix of all actions with equal weighting
- **Aggressive** - Prioritizes attack and focus actions
- **Defensive** - Emphasis on defend and recover actions
- **Counter** - Focuses on counter-attacks
- **Pirate** - Special strategy using blade_flurry attacks
- **Summoner** - Focuses on summoning minions
- **Attack** - Pure offensive, only attack actions

Strategy logic (HeroBattleService.php:347-399):
- Respects action limitations (limited actions can't repeat)
- Adjusts based on health (low health prioritizes recovery)
- Considers focus status (won't focus if already focused)
- Won't recover if at full health

### Turn Processing (HeroBattleService.php:245-345)

**Turn Flow:**
1. Check time banks for all combatants
2. Wait until all living combatants are "ready" (have an action set)
3. Determine actions (from queue or automated strategy)
4. Process all actions simultaneously
5. Apply damage/healing, respecting shield absorption
6. Process post-combat effects (abilities that trigger on damage/death)
7. Process status effects (periodic abilities, resurrection, etc.)
8. Check for winner conditions
9. Prepare for next turn or end battle
10. Recursively process next turn if all combatants still ready

**Winner Conditions:**
- All combatants eliminated: Draw
- All player heroes eliminated, NPC remains: NPC wins (no rating change)
- Single combatant remains: That combatant wins
- Last player standing: Player wins

### Special Combat Abilities

Combatants can have special abilities that modify combat behavior. These are stored in the `abilities` JSON column on the `hero_combatants` table.

#### Offensive Abilities
- **lifesteal** (HeroBattleService.php:513-517)
  - Heals for 50% of damage dealt on successful attacks
  - Triggers on any attack that deals damage

- **crushing_blow** (HeroBattleService.php:394-396)
  - Automatically upgrades basic attacks to crushing blows
  - Enhanced damage calculation

- **blade_flurry**
  - Used by Rebel Corsairs and Admiral
  - Multiple rapid strikes in succession

#### Defensive Abilities
- **elusive** (HeroBattleService.php:457-459)
  - Completely evades attacks when opponent is not focused (0% damage instead of 50%)
  - Used by Nightbringer and Tome of Power
  - Countered by focus attacks

- **hardiness** (HeroBattleService.php:820-824)
  - One-time survival at 1 HP when taking fatal damage
  - Ability is consumed after triggering
  - Prevents death in critical moments

- **undying** (HeroBattleService.php:928-947)
  - Resurrects after 5 turns at 50% health
  - Used by Warrior King, Sorcerer King, Betrayer King, Eternal Guardian
  - Status counter tracks turns until resurrection
  - Max health reduced to 50% upon revival

#### Support Abilities
- **channeling** (HeroBattleService.php:416-421, 537-542)
  - Maintains focus bonus without consuming it on attacks
  - Can stack focus bonus when using focus action while already focused
  - Allows sustained high-damage output

- **mending** (HeroBattleService.php:566-568)
  - Enhances recover action when focused
  - Consumes focus to increase healing

#### Tactical Abilities
- **darkness** (HeroBattleService.php:360-366, 950-955)
  - Periodic evasion buff every N turns
  - Used by Nightbringer
  - Status message shown when active
  - Only activates if evasion is below 100

- **summon_skeleton** (HeroBattleService.php:369-374, 958-963)
  - Periodically spawns skeleton warriors
  - Used by Eternal Guardian
  - Summons appear as new combatants mid-battle
  - Warning message shown turn before summoning

- **dying_light** (HeroBattleService.php:802-818)
  - Explodes on death to debuff specific enemy
  - Used by Nox Cultists to expose Nightbringer
  - Reduces Nightbringer's evasion to 0 when cultist dies
  - Ability consumed on death

- **power_source** (HeroBattleService.php:827-858)
  - When this combatant dies, weakens a linked target
  - Configuration stored in `status` JSON field
  - Specifies target name and stat reductions
  - Used by Tome of Power to protect Lich King
  - Example: `{'target_name': 'Lich King', 'stat_reductions': {'defense': 10, 'evasion': 25}}`

- **enrage**
  - Increases attack power when health is low
  - Used by Rebel Admiral and Lich King

#### Phase-Cycling Abilities (HeroBattleService.php:863-920, 965-1002)
Advanced ability system where effects change over time based on turn count:

**Mechanism:**
- Abilities can have multiple "phases" that activate at different times
- Each phase can grant different abilities to self and allies
- Can cycle back to phase 1 or stay at max phase
- Configuration includes:
  - `turns_per_phase`: Turns before advancing to next phase
  - `max_phase`: Maximum phase number
  - `cycle_phases`: Whether to loop back to phase 1
  - `phases`: Array of phase configurations

**Phase Configuration:**
- `self_abilities`: Abilities granted to the combatant
- `ally_abilities`: Abilities granted to allied NPCs
- `message`: Flavor text displayed on phase transition

**Example** (tome_of_power ability):
```
'phases' => [
    1 => ['self_abilities' => ['shield_regen'], 'message' => 'The tome glows...'],
    2 => ['self_abilities' => ['shield_regen', 'enhanced_defense'], 'message' => 'Power surges!'],
    ...
]
```

**Technical Details:**
- Base abilities stored when first phase change occurs
- Each phase change merges base abilities with phase-specific abilities
- Allied NPCs also receive ability updates
- Phase state tracked in `status` JSON field
- Only applies to living combatants

### Enemy Encounter System (HeroEncounterHelper.php)

**Enemy Stat Definitions:**
Enemies have predefined stat templates including:
- Base combat stats (health, attack, defense, evasion, etc.)
- Automated strategy preference
- Special abilities array
- Optional status configuration for complex mechanics

**Encounter Composition:**
Named encounters combine multiple enemies with specific configurations:
- Enemy types and counts
- Custom names for each instance
- Source/lore information
- Linked mechanics (like Tome protecting Lich King)

**Difficulty Scaling:**
- Early raids: Simple single enemies or small groups
- Mid-tier raids: Multiple enemies with basic abilities
- End-game raids: Bosses with complex ability interactions
  - The Nightbringer: Requires specific strategy (kill cultists to expose boss)
  - The Lich King: Must destroy Tome of Power first to weaken boss

## Services and Calculators

**HeroActionService** (src/Services/Dominion/Actions/HeroActionService.php):
- Hero creation and class changing
- Upgrade unlocking
- Combat action management

**HeroBattleService** (src/Services/Dominion/HeroBattleService.php):
- Battle creation and management
- Combat processing and turn resolution
- Matchmaking queue management

**HeroTournamentService** (src/Services/Dominion/HeroTournamentService.php):
- Tournament organization and management

**HeroCalculator** (src/Calculators/Dominion/HeroCalculator.php):
- Experience and level calculations
- Passive bonus calculations
- Combat stat computation
- Damage and healing calculations

**HeroHelper** (src/Helpers/HeroHelper.php):
- Class definitions and metadata
- Upgrade descriptions and tooltips
- Combat action validation and definitions
- Combat strategy configurations
- Display utilities

**HeroEncounterHelper** (src/Helpers/HeroEncounterHelper.php):
- Enemy stat definitions and templates
- Named encounter configurations
- Practice battle opponent creation
- Raid boss mechanics

## Database Schema

**heroes table:**
- Basic info: dominion_id, name, class, experience
- Combat stats: stat_combat_wins, stat_combat_losses, stat_combat_draws
- Combat rating for matchmaking

**hero_upgrades table:**
- Upgrade definitions: key, name, level, type, icon, classes, active

**hero_upgrade_perks table:**
- Individual effects: hero_upgrade_id, key, value

**hero_hero_upgrades table:**
- Junction linking heroes to unlocked upgrades

**Battle-related tables:**
- **hero_battles**: Battle instances
  - Fields: round_id, winner_combatant_id, current_turn, finished, pvp, raid_tactic_id
  - Tracks overall battle state and configuration
- **hero_combatants**: Battle participants with stats and state
  - Combat stats: health, attack, defense, evasion, focus, counter, recover
  - Current state: current_health, current_action, last_action, has_focus
  - **New in latest**: abilities (JSON array), shield (integer), status (JSON object)
  - Time management: time_bank, last_processed_at, automated, strategy
  - Actions queue: actions (JSON array) for queued player commands
- **hero_battle_actions**: Turn-by-turn action log
  - Records each action taken with damage, healing, and description
  - Links combatant to target for hostile actions
- **hero_battle_queue**: Matchmaking queue
  - Temporary queue entries for PvP matchmaking
  - Auto-expires after 1 hour

**Tournament tables:**
- hero_tournaments: Tournament events
- hero_tournament_participants: Tournament entries
- hero_tournament_battles: Tournament matches

## Game Integration

Heroes integrate with the main game through:
- **Passive bonuses** applied to dominion calculations
- **Experience gain** from various game activities with retention across class changes
- **Battle system** for competitive PvP gameplay
- **Tournament events** for organized competition
- **Upgrade unlocking** for character progression
- **Advanced class requirements** tied to dominion achievements

The system is designed to provide both passive gameplay benefits through class bonuses and active gameplay through the combat and tournament systems.

## Recent Changes

### Round 47 - Enhanced Combat System

**Major Combat Overhaul:**
- **Practice Battle System** - New hero-battle-practice.blade.php view
  - Fight against "Evil Twin" clone or named encounters
  - 9+ unique boss encounters from raids and events
  - Non-rated battles for testing strategies
- **Special Abilities System** - New `abilities` JSON column on hero_combatants
  - 15+ unique combat abilities (lifesteal, elusive, undying, etc.)
  - Phase-cycling abilities that evolve over turns
  - Ability interactions (dying_light exposes Nightbringer)
- **Shield System** - New `shield` column on hero_combatants
  - Temporary damage absorption before health
  - Can be granted by stat actions or abilities
- **Enhanced Combat Actions**:
  - Volatile actions (high-risk/high-reward with backfire)
  - Flurry actions (multiple attacks with penalty)
  - Summon actions (spawn allies mid-battle)
  - Stat actions (buff/debuff mechanics)
- **HeroEncounterHelper** - New helper class (src/Helpers/HeroEncounterHelper.php)
  - Defines all enemy templates and stats
  - Manages encounter compositions
  - Supports complex boss mechanics (linked enemies, phases)
- **Multi-Enemy Battles** - Support for 3+ combatants
  - Target selection for hostile actions in UI
  - Automatic targeting for automated strategies
  - Complex encounters with minion spawning

**Database Migrations:**
- `2025_09_13_180323_add_abilities_to_hero_combatants_table.php`
- `2025_09_25_025703_add_shield_to_hero_combatants_table.php`

**Raid Integration:**
- Hero battles can now be initiated from raid tactics
- Winning hero battles earns raid points
- New `raid_tactic_id` field on hero_battles table
- Points calculated based on realm activity

**UI Improvements:**
- Practice battle selection screen with encounter list
- Special ability tooltips in battle view
- Shield display in health bars (aqua colored)
- Time bank display shows hours and minutes remaining

### Round 46 - Hero Class Changing System
- **Terminology Update**: "Retiring a hero" has been changed to "changing hero class" to better reflect the mechanics
- **Class-Specific Confirmation**: The class change page now shows a confirmation specific to the target class
- **Route Changes**: 
  - `GET /heroes/class/{class}` - Shows confirmation page for changing to specific class
  - `POST /heroes/class/{class}` - Processes the class change to specific class
- **Experience Handling**: 
  - Heroes retain their previous experience if switching back to a previously played class
  - Heroes start at 0 XP when switching to a completely new class
  - No more "half XP" penalty system
- **Class Change Cooldown**: 
  - 48-hour cooldown implemented between class changes
  - Prevents frequent class switching for strategic advantage
  - Cooldown information displayed in UI with remaining hours
- **UI Improvements**: 
  - Class selection table now shows "Current" for active class instead of disabled "Select"
  - Individual "Select" links for each class that go to class-specific confirmation pages
  - Updated information text to reflect new mechanics

### Technical Changes
- **Controller Methods**: 
  - `getRetireHero()` → `getChangeClass(Request $request, string $class)`
  - `postRetireHero()` → `postChangeClass(HeroCreateActionRequest $request, string $class)`
- **Service Methods**: 
  - `HeroActionService::retire()` → `HeroActionService::changeClass()`
- **View Files**: 
  - `retire.blade.php` → `change-class.blade.php` (with git mv)
- **History Event**: 
  - `EVENT_ACTION_HERO_RETIRE` → `EVENT_ACTION_HERO_CLASS_CHANGE`
- **Success Messages**: Updated to use "changed to X class" instead of "retired and selected"
- **Database Schema**: 
  - Added `last_class_change_at` timestamp column to heroes table
  - Tracks when the last class change occurred for cooldown validation
- **Calculator Methods**: 
  - `HeroCalculator::canChangeClass(Hero $hero)` - Check if hero can change class
  - `HeroCalculator::hoursUntilClassChange(Hero $hero)` - Get remaining cooldown hours
  - `CLASS_CHANGE_COOLDOWN_HOURS` constant set to 48 hours