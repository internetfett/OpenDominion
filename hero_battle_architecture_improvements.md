# Hero Battle System Architecture Improvements

## Current Architecture Analysis

### Current Strengths
1. **Clear separation of concerns** - Services, Calculators, and Helpers are well-organized
2. **Data-driven action definitions** - Combat actions defined as arrays in HeroHelper
3. **Flexible processor pattern** - Different action types use different processors

### Current Pain Points

#### 1. **Hardcoded Ability Logic in HeroBattleService**
```php
// Example: Abilities scattered throughout the service
if (in_array('elusive', $target->abilities ?? []) && !$combatant->has_focus) {
    $evadeMultiplier = 0;
}

if (in_array('lifesteal', $combatant->abilities ?? [])) {
    $healing = round($damage / 2);
    $health += $healing;
}

if (in_array('darkness', $combatant->abilities ?? []) && $combatant->evasion < 100) {
    $actionDef = $this->heroHelper->getCombatActions()->get('darkness');
    if ((($combatant->battle->current_turn - 1) % $actionDef['attributes']['turns']) == 0) {
        return ['action' => 'darkness', 'target' => null];
    }
}
```

**Problems:**
- Abilities are checked with `in_array()` scattered across multiple methods
- Logic is intertwined with battle processing
- No single source of truth for ability behavior
- Difficult to test individual abilities
- Adding new abilities requires modifying HeroBattleService

#### 2. **Passive Abilities Mixed with Active Actions**
```php
'channeling' => [
    'name' => 'Channeling',
    'processor' => null,  // Passive ability, no processor
    'type' => 'passive',
    'limited' => false,
    'special' => true,
    'class' => 'sorcerer',
],
```

**Problems:**
- Passive abilities defined alongside active actions
- No consistent way to apply passive effects
- Passive logic hardcoded in various processor methods

#### 3. **Phase-Cycling Abilities Lack Abstraction**
```php
// Phase cycling logic embedded in processStatus()
foreach ($combatant->abilities ?? [] as $abilityKey) {
    $actionDef = $this->heroHelper->getCombatActions()->get($abilityKey);
    if (!$actionDef || !isset($actionDef['attributes']['phases'])) {
        continue;
    }
    // 40+ lines of phase cycling logic
}
```

**Problems:**
- Phase cycling is a single massive method
- Hard to create new phase-based abilities
- No reusable phase cycling component

#### 4. **Encounter Definitions Lack Structure**
```php
// Encounters defined as nested arrays
'nightbringer' => [
    'name' => 'The Nightbringer',
    'source' => 'Raid (Rise of the Nightbringer)',
    'enemies' => [
        ['key' => 'nightbringer', 'name' => 'The Nightbringer'],
        ['key' => 'nox_cultist', 'name' => 'Nox Cultist #1'],
        ['key' => 'nox_cultist', 'name' => 'Nox Cultist #2'],
    ],
],
```

**Problems:**
- No validation of encounter definitions
- Hard to create complex encounter mechanics
- No encounter builders or templates
- Difficult to test encounters in isolation

#### 5. **Ability Dependencies Not Explicit**
```php
// dying_light ability needs to find Nightbringer
if (in_array('dying_light', $combatant->abilities ?? []) && $combatant->current_health <= 0) {
    $nightbringer = $combatant->battle->combatants
        ->where('name', 'The Nightbringer')
        ->first();
    // ...
}
```

**Problems:**
- Dependencies on specific enemy names hardcoded
- No way to configure ability relationships
- Brittle if encounter structure changes

#### 6. **No Team/Alliance Support**
```php
// Current system assumes simple PvP or Player vs NPCs
// No concept of teams or cooperative play
```

**Problems:**
- Cannot support 2v2, 3v3, or cooperative battles
- No team-based abilities (buffs for allies, team heals, etc.)
- No team victory conditions
- Targeting system doesn't differentiate allies from enemies
- No support for team strategies or coordination

## Proposed Architecture Improvements

### 1. **Ability System Refactor**

Create a proper ability system with:
- Abstract `Ability` base class
- Concrete ability classes for each ability
- Ability registry/manager
- Clear lifecycle hooks

#### Structure:
```
src/
  Domain/
    HeroBattle/
      Abilities/
        AbstractAbility.php           # Base class
        AbilityInterface.php          # Interface
        AbilityRegistry.php           # Registry pattern
        Traits/
          ModifiesDamage.php          # Reusable traits
          TriggersOnDeath.php
          PeriodicEffect.php
        Passive/
          Elusive.php
          Lifesteal.php
          Channeling.php
          Hardiness.php
        Active/
          VolatileMixture.php
          BladeFlurry.php
          ShadowStrike.php
        Team/
          GroupHeal.php
          Inspiration.php
          Sacrifice.php
        Special/
          DyingLight.php
          PowerSource.php
          Undying.php
        Phased/
          TomeOfPower.php
          AbstractPhasedAbility.php
```

#### Benefits:
- Each ability is a class with clear responsibilities
- Easy to unit test individual abilities
- Abilities can be composed and reused
- Clear hooks: `onAttack()`, `onDamage()`, `onDeath()`, `onTurnStart()`, etc.
- Abilities self-register with the registry

### 2. **Event-Driven Combat System**

Introduce combat events that abilities can listen to:

```php
// Events
CombatEvents:
- BeforeAttack
- AfterAttack
- BeforeDamage
- AfterDamage
- OnDeath
- OnTurnStart
- OnTurnEnd
- OnActionQueued
```

#### Benefits:
- Abilities subscribe to events they care about
- No more scattered `in_array()` checks
- HeroBattleService becomes simpler orchestration
- Easy to add new abilities without modifying service

### 3. **Ability Configuration System**

Separate ability data from logic:

```php
// config/hero_abilities.php or database table
return [
    'lifesteal' => [
        'class' => Lifesteal::class,
        'config' => [
            'heal_percent' => 0.5,
        ],
        'display_name' => 'Lifesteal',
        'description' => 'Heals for 50% of damage dealt',
        'icon' => 'ra-heart-bottle',
    ],
];
```

#### Benefits:
- Easy to tune ability parameters
- Configuration can be data-driven
- Support for ability variants with different configs

### 4. **Ability Composition System**

Allow abilities to be composed from smaller pieces:

```php
// Example: Creating a new ability by composition
class FrostStrike extends AbstractAbility implements ModifiesDamage
{
    use DealsDamage, AppliesDebuff;

    public function onAttack(CombatContext $context): void
    {
        $damage = $this->calculateDamage($context);
        $this->applyDamage($damage, $context->target);

        if ($this->shouldApplyDebuff()) {
            $this->applyDebuff('attack', -5, $context->target);
        }
    }
}
```

#### Benefits:
- Reusable components
- Consistent behavior across similar abilities
- Less code duplication

### 5. **Combat Context Object**

Replace scattered parameters with context object:

```php
class CombatContext
{
    public HeroCombatant $attacker;
    public HeroCombatant $target;
    public HeroBattle $battle;
    public array $actionDef;
    public int $damage = 0;
    public int $healing = 0;
    public bool $evaded = false;
    public bool $countered = false;
    public array $messages = [];

    // Helper methods
    public function addMessage(string $message): void
    public function hasFocus(): bool
    public function isDefending(): bool
}
```

#### Benefits:
- Single object to pass around
- Clear what data is available
- Easy to extend with new properties
- Immutable variants for predictability

### 6. **Team-Based Combat System**

Add support for team battles (2v2 and cooperative vs NPCs):

#### Database Schema Changes

```php
// Add team_id to hero_combatants table
Schema::table('hero_combatants', function (Blueprint $table) {
    $table->integer('team_id')->nullable()->after('hero_battle_id');
    $table->index(['hero_battle_id', 'team_id']);
});

// Add battle_type to hero_battles table
Schema::table('hero_battles', function (Blueprint $table) {
    $table->string('battle_type')->default('pvp'); // 'pvp', '2v2', 'cooperative'
    $table->integer('winning_team_id')->nullable();
});
```

#### Team Management System

```php
src/
  Domain/
    HeroBattle/
      Teams/
        Team.php                    # Team entity
        TeamManager.php             # Team composition & lifecycle
        TeamMatcher.php             # Matchmaking for teams
```

**Team Class:**
```php
class Team
{
    protected int $id;
    protected Collection $members;

    public function getMembers(): Collection
    public function getLivingMembers(): Collection
    public function getAllies(HeroCombatant $combatant): Collection
    public function getEnemies(HeroBattle $battle): Collection
    public function isAlly(HeroCombatant $a, HeroCombatant $b): bool
    public function hasLivingMembers(): bool
}
```

#### Targeting System Enhancements

```php
// Enhanced CombatContext
class CombatContext
{
    public HeroCombatant $attacker;
    public HeroCombatant $target;
    public HeroBattle $battle;
    public ?Team $attackerTeam;
    public ?Team $targetTeam;

    // New targeting helpers
    public function isAlly(): bool
    public function isEnemy(): bool
    public function getAllies(): Collection
    public function getEnemies(): Collection
    public function getLivingAllies(): Collection
    public function getLivingEnemies(): Collection
    public function getRandomEnemy(): ?HeroCombatant
    public function getRandomAlly(): ?HeroCombatant
}

// Enhanced action definitions
'heal_ally' => [
    'name' => 'Heal Ally',
    'processor' => 'heal',
    'type' => 'friendly',  // New type for team abilities
    'target_type' => 'ally', // Specifies valid targets
    'limited' => true,
    'messages' => [
        'heal' => '%s heals %s for %s health.'
    ]
],
```

#### Team-Based Abilities

```php
// Abilities that affect allies
src/Domain/HeroBattle/Abilities/Team/
  GroupHeal.php           # Heal all allies
  TeamBuff.php            # Buff all allies
  Sacrifice.php           # Take damage for ally
  Coordination.php        # Team-wide counter bonus
  SharedShield.php        # Distribute shield to team

// Example: Group Heal
class GroupHeal extends AbstractAbility implements TargetsAllies
{
    public function execute(CombatContext $context): void
    {
        $allies = $context->getLivingAllies();
        $healAmount = $this->config['base_heal'];

        foreach ($allies as $ally) {
            $this->healCombatant($ally, $healAmount);
            $context->addMessage("{$context->attacker->name} heals {$ally->name} for {$healAmount} health.");
        }
    }
}

// Example: Sacrifice
class Sacrifice extends AbstractAbility implements TargetsAllies
{
    public function beforeDamageReceived(CombatContext $context): void
    {
        // If ally would take lethal damage, redirect to self
        if ($context->target !== $context->attacker &&
            $context->willBeLethal() &&
            $this->canSacrifice()) {

            $originalTarget = $context->target;
            $context->target = $context->attacker;
            $context->damage = round($context->damage * 0.75); // Reduce damage taken
            $context->addMessage("{$context->attacker->name} sacrifices themselves to protect {$originalTarget->name}!");
        }
    }
}
```

#### Battle Creation with Teams

```php
// Service method for creating team battles
class HeroBattleService
{
    public function create2v2Battle(array $team1, array $team2): HeroBattle
    {
        $battle = HeroBattle::create([
            'round_id' => $team1[0]->dominion->round_id,
            'battle_type' => '2v2',
            'pvp' => true,
        ]);

        // Create team 1
        foreach ($team1 as $dominion) {
            $combatant = $this->createCombatant($battle, $dominion->hero);
            $combatant->team_id = 1;
            $combatant->save();
        }

        // Create team 2
        foreach ($team2 as $dominion) {
            $combatant = $this->createCombatant($battle, $dominion->hero);
            $combatant->team_id = 2;
            $combatant->save();
        }

        return $battle;
    }

    public function createCooperativeBattle(array $players, string $encounterKey): HeroBattle
    {
        $battle = HeroBattle::create([
            'round_id' => $players[0]->dominion->round_id,
            'battle_type' => 'cooperative',
            'pvp' => false,
        ]);

        // Create player team
        foreach ($players as $dominion) {
            $combatant = $this->createCombatant($battle, $dominion->hero);
            $combatant->team_id = 1;
            $combatant->save();
        }

        // Create NPC enemies (team 2)
        $encounter = $this->encounterHelper->getEncounters()->get($encounterKey);
        foreach ($encounter['enemies'] as $enemy) {
            $enemyStats = $this->encounterHelper->getEnemies()->get($enemy['key']);
            $enemyStats['name'] = $enemy['name'];
            $combatant = $this->createNonPlayerCombatant($battle, $enemyStats);
            $combatant->team_id = 2;
            $combatant->save();
        }

        return $battle;
    }
}
```

#### Victory Conditions for Teams

```php
protected function checkTeamVictory(HeroBattle $battle): void
{
    $teams = $battle->combatants
        ->groupBy('team_id')
        ->map(fn($members) => $members->where('current_health', '>', 0));

    $livingTeams = $teams->filter(fn($members) => $members->count() > 0);

    if ($livingTeams->count() === 0) {
        // Draw - all teams eliminated
        $this->setTeamWinner($battle, null);
    } elseif ($livingTeams->count() === 1) {
        // Single team remains
        $winningTeamId = $livingTeams->keys()->first();
        $this->setTeamWinner($battle, $winningTeamId);
    }
}

protected function setTeamWinner(HeroBattle $battle, ?int $winningTeamId): void
{
    $battle->winning_team_id = $winningTeamId;
    $battle->finished = true;
    $battle->save();

    if ($winningTeamId) {
        $winners = $battle->combatants->where('team_id', $winningTeamId);
        $losers = $battle->combatants->where('team_id', '!=', $winningTeamId);

        foreach ($winners as $winner) {
            if ($winner->hero !== null) {
                $winner->hero->increment('stat_combat_wins');
            }
        }

        foreach ($losers as $loser) {
            if ($loser->hero !== null) {
                $loser->hero->increment('stat_combat_losses');
            }
        }

        if ($battle->pvp) {
            $this->updateTeamRatings($battle, $winningTeamId);
        }
    }
}
```

#### Team Matchmaking

```php
// Enhanced queue system for team battles
Schema::create('hero_battle_team_queue', function (Blueprint $table) {
    $table->id();
    $table->integer('round_id');
    $table->string('battle_type'); // '2v2', 'cooperative'
    $table->json('hero_ids'); // Array of hero IDs in party
    $table->integer('avg_rating');
    $table->integer('avg_level');
    $table->timestamps();
});

class TeamMatcher
{
    public function findMatch(array $heroes, string $battleType): ?array
    {
        $avgRating = collect($heroes)->avg('combat_rating');
        $avgLevel = collect($heroes)->avg(fn($h) => $this->heroCalculator->getHeroLevel($h));

        // Find another team with similar rating/level
        $match = HeroBattleTeamQueue::query()
            ->where('battle_type', $battleType)
            ->where('avg_rating', '>=', $avgRating - 100)
            ->where('avg_rating', '<=', $avgRating + 100)
            ->where('avg_level', '>=', $avgLevel - 2)
            ->where('avg_level', '<=', $avgLevel + 2)
            ->first();

        return $match?->hero_ids;
    }
}
```

#### UI for Team Battles

```blade
<!-- Team selection interface -->
<div class="team-formation">
    <h3>Form Your Team</h3>

    <!-- Party slots -->
    <div class="party-slots">
        <div class="slot">
            <strong>You</strong>
            {{ $hero->name }} - Level {{ $heroCalculator->getHeroLevel($hero) }}
        </div>

        <div class="slot">
            <select name="teammate_1">
                <option value="">Empty Slot</option>
                @foreach ($availableRealmates as $realmate)
                    <option value="{{ $realmate->id }}">
                        {{ $realmate->name }} - {{ $realmate->hero->name }} (Lvl {{ $level }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Battle type selection -->
    <div class="battle-type">
        <label>
            <input type="radio" name="type" value="2v2" checked>
            2v2 Ranked
        </label>
        <label>
            <input type="radio" name="type" value="cooperative">
            Cooperative vs Boss
        </label>
    </div>

    <button type="submit">Queue for Battle</button>
</div>
```

#### Benefits of Team System:
- **Cooperative PvE**: Players team up against tough raid bosses
- **Ranked Team PvP**: 2v2 competitive battles
- **Social Gameplay**: Encourages realm cooperation
- **New Ability Space**: Team buffs, heals, protection abilities
- **Scaling Difficulty**: Bosses can be balanced for teams
- **Tournaments**: Team-based tournaments

#### Battle Type Examples:

**2v2 PvP:**
- Team 1: Alice + Bob vs Team 2: Carol + Dave
- Ranked with team ELO
- Shared victory/defeat

**Cooperative:**
- Team: 2 players vs The Ancient Dragon + 2 minions
- Non-rated, earns raid points for all participants
- Requires coordination to defeat boss

## Implementation Plan

### Phase 1: Foundation (Week 1)
- [ ] Create base ability interfaces and abstract classes in `src/Domain/HeroBattle/Abilities/`
- [ ] Create AbilityRegistry
- [ ] Create CombatContext object
- [ ] Add combat events system (CombatEventDispatcher)
- [ ] Create `src/Helpers/HeroAbilityHelper.php` for ability definitions
- [ ] Write comprehensive tests for foundation

### Phase 2: Migrate Passive Abilities (Week 2)
- [ ] Convert passive abilities to classes (lifesteal, elusive, channeling, etc.)
- [ ] Update HeroBattleService to use ability registry
- [ ] Remove hardcoded passive ability checks
- [ ] Test passive abilities thoroughly with per-combatant configs

### Phase 3: Migrate Active Abilities (Week 3)
- [ ] Convert active abilities to classes
- [ ] Create ability composition traits
- [ ] Update action processors to use ability classes
- [ ] Test active abilities with different configurations

### Phase 4: Migrate Special Abilities (Week 4)
- [ ] Convert death-triggered abilities (dying_light, power_source, hardiness)
- [ ] Convert phased abilities (tome_of_power)
- [ ] Convert periodic abilities (darkness, summon_skeleton, undying)
- [ ] Test complex ability interactions

### Phase 5: Team System Foundation (Week 5)
- [ ] Add team_id database column to hero_combatants
- [ ] Add battle_type and winning_team_id to hero_battles
- [ ] Create Team, TeamManager, TeamMatcher classes in `src/Domain/HeroBattle/Teams/`
- [ ] Update CombatContext with team support
- [ ] Create team victory condition logic
- [ ] Test basic team mechanics

### Phase 6: Team Abilities (Week 6)
- [ ] Create team-targeted abilities (heal ally, buff ally, etc.)
- [ ] Create support abilities (Sacrifice, GroupHeal)
- [ ] Update targeting system for friendly/hostile
- [ ] Test team ability interactions

### Phase 7: Team Battle Types (Week 7)
- [ ] Implement 2v2 battle creation
- [ ] Implement cooperative battle creation (2 players vs NPCs)
- [ ] Create team matchmaking queue
- [ ] Add team battle UI
- [ ] Create team formation interface
- [ ] Test 2v2 and cooperative compositions

### Phase 8: Team Encounters (Week 8)
- [ ] Update encounter definitions in HeroEncounterHelper for team battles
- [ ] Create cooperative raid encounters designed for 2 players
- [ ] Balance multi-player boss fights
- [ ] Add team-oriented enemy abilities
- [ ] Test cooperative encounters

### Phase 9: Polish & Documentation (Week 9)
- [ ] Create ability creation guide
- [ ] Create encounter creation guide
- [ ] Create team battle guide
- [ ] Performance optimization (ability caching, batched DB saves)
- [ ] Final integration tests
- [ ] Balance tuning for team vs solo

## Success Metrics

### Developer Experience
- **Before**: Adding a new ability requires modifying 3-5 files and 50-100 lines of code
- **After**: Adding a new ability requires creating 1 file with 20-30 lines of code

### Maintainability
- **Before**: Ability logic scattered across HeroBattleService (1100+ lines)
- **After**: HeroBattleService orchestration only (300-400 lines), abilities isolated

### Testability
- **Before**: Testing abilities requires full battle simulation
- **After**: Abilities can be unit tested in isolation

### Extensibility
- **Before**: Complex abilities require deep knowledge of battle system
- **After**: Abilities extend base classes and implement clear interfaces

### Team Combat Support
- **Before**: Only 1v1 or 1vN (player vs NPCs) supported
- **After**: Supports 2v2 and cooperative (2 players vs NPCs)

### Battle Type Variety
- **Before**: PvP, Practice, Tournament (all solo-focused)
- **After**: +Cooperative Raid, +Team PvP (2v2)

## Example: Before and After

### Before (Current)
```php
// In HeroBattleService::processAttackAction()
if (in_array('lifesteal', $combatant->abilities ?? []) && $damage > 0) {
    $healing = round($damage / 2);
    $health += $healing;
    $description .= sprintf(' %s heals for %s health.', $combatant->name, $healing);
}

// In HeroBattleService::processAttackAction()
if (in_array('elusive', $target->abilities ?? []) && !$combatant->has_focus) {
    $evadeMultiplier = 0;
}

// In HeroBattleService::processPostCombat()
if (in_array('hardiness', $combatant->abilities ?? []) && $combatant->current_health < 1) {
    $combatant->current_health = 1;
    $this->spendAbility($combatant, 'hardiness');
    return " {$combatant->name} clings to life with 1 health.";
}
```

### After (Proposed)
```php
// src/Domain/HeroBattle/Abilities/Passive/Lifesteal.php
namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

class Lifesteal extends AbstractAbility implements ModifiesDamage
{
    public function afterDamageDealt(CombatContext $context): void
    {
        if ($context->damage > 0) {
            $healing = round($context->damage * $this->config['heal_percent']);
            $context->healing += $healing;
            $context->addMessage("{$context->attacker->name} heals for {$healing} health.");
        }
    }
}

// src/Domain/HeroBattle/Abilities/Passive/Elusive.php
namespace OpenDominion\Domain\HeroBattle\Abilities\Passive;

class Elusive extends AbstractAbility implements ModifiesEvasion
{
    public function beforeDamageReceived(CombatContext $context): void
    {
        if (!$context->attacker->has_focus) {
            $context->evadeMultiplier = 0; // Complete evasion
        }
    }
}

// src/Domain/HeroBattle/Abilities/Special/Hardiness.php
namespace OpenDominion\Domain\HeroBattle\Abilities\Special;

class Hardiness extends AbstractAbility implements TriggersOnDeath
{
    public function beforeDeath(CombatContext $context): bool
    {
        if ($this->charges > 0) {
            $context->target->current_health = 1;
            $this->consume();
            $context->addMessage("{$context->target->name} clings to life with 1 health.");
            return false; // Prevent death
        }
        return true; // Allow death
    }
}

// HeroBattleService just fires events
$this->eventDispatcher->dispatch(new BeforeDamageReceived($context));
$this->eventDispatcher->dispatch(new AfterDamageDealt($context));
```

### Team Combat Example

**Creating a Cooperative Battle:**

```php
// Before: Not possible
// After: Simple and clean

// Controller
public function startCooperativeBattle(Request $request)
{
    $teammates = Dominion::whereIn('id', $request->teammate_ids)->get();
    $encounter = $request->encounter;

    $battle = $this->heroBattleService->createCooperativeBattle(
        $teammates,
        $encounter
    );

    return redirect()->route('dominion.heroes.battles.view', $battle);
}

// Creating a 2v2 battle from matchmaking
$team1 = [$dominion1, $dominion2];
$team2 = $this->teamMatcher->findMatch($team1, '2v2');

if ($team2) {
    $battle = $this->heroBattleService->create2v2Battle($team1, $team2);
}
```

**Team Ability in Action:**

```php
// src/Domain/HeroBattle/Abilities/Team/GroupHeal.php
namespace OpenDominion\Domain\HeroBattle\Abilities\Team;

// Healer class ability: GroupHeal
// Heals all allies for 20 HP

// Before: Would require massive changes to HeroBattleService
// After: Simple ability class

class GroupHeal extends AbstractAbility implements TargetsAllies
{
    public function execute(CombatContext $context): void
    {
        foreach ($context->getLivingAllies() as $ally) {
            $this->healCombatant($ally, $this->config['heal_amount']);
            $context->addMessage(
                "{$context->attacker->name} heals {$ally->name} for {$this->config['heal_amount']} health."
            );
        }
    }

    public function getCooldown(): int
    {
        return 3; // Can use every 3 turns
    }
}

// Registering the ability in HeroAbilityHelper.php
// src/Helpers/HeroAbilityHelper.php
'group_heal' => [
    'class' => GroupHeal::class,
    'config' => ['heal_amount' => 20],
    'display_name' => 'Group Heal',
    'description' => 'Heals all allies for 20 health',
    'icon' => 'ra-health',
    'hero_class' => 'healer', // Only healers get this
],
```

## Next Steps

1. **Review and approve architecture**
2. **Create proof-of-concept with 2-3 abilities**
3. **Evaluate POC and iterate on design**
4. **Begin phased implementation**
5. **Create migration path for existing abilities**

## Notes

- Maintain backward compatibility during migration
- Consider performance implications (event dispatching overhead)
- Ensure database schema supports new architecture
- Plan for ability versioning (if abilities change between rounds)
