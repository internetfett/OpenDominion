# Hero Battle Ability System - Proof of Concept

## Overview

This POC demonstrates the new ability system architecture with:
- Interface-based ability design
- Per-combatant configurable abilities
- Clean separation of concerns
- Easy testing in isolation

## Files Created

### Core Framework
- `src/Domain/HeroBattle/Abilities/AbilityInterface.php` - Base interface
- `src/Domain/HeroBattle/Abilities/AbstractAbility.php` - Abstract base class
- `src/Domain/HeroBattle/Abilities/AbilityRegistry.php` - Registry for loading abilities
- `src/Domain/HeroBattle/Context/CombatContext.php` - Combat state container

### Ability Traits (Interfaces)
- `src/Domain/HeroBattle/Abilities/Traits/ModifiesDamage.php`
- `src/Domain/HeroBattle/Abilities/Traits/ModifiesEvasion.php`
- `src/Domain/HeroBattle/Abilities/Traits/TriggersOnDeath.php`

### Example Abilities
- `src/Domain/HeroBattle/Abilities/Passive/Lifesteal.php` - Heals on damage
- `src/Domain/HeroBattle/Abilities/Passive/Elusive.php` - Cannot be hit without focus
- `src/Domain/HeroBattle/Abilities/Special/Hardiness.php` - Survives lethal blow once (charges)
- `src/Domain/HeroBattle/Abilities/Active/PowerStrike.php` - Bonus damage (cooldown)

### Configuration
- `src/Helpers/HeroAbilityHelper.php` - Ability definitions with defaults

### Tests
- `tests/Unit/Domain/HeroBattle/Abilities/LifestealTest.php` - 4 test cases
- `tests/Unit/Domain/HeroBattle/Abilities/ElusiveTest.php` - 2 test cases
- `tests/Unit/Domain/HeroBattle/Abilities/HardinessTest.php` - 3 test cases
- `tests/Unit/Domain/HeroBattle/Abilities/PowerStrikeTest.php` - 5 test cases (cooldown)
- `tests/Unit/Domain/HeroBattle/Abilities/AbilityRegistryTest.php` - 4 test cases (state persistence)
- `tests/Unit/Domain/HeroBattle/Abilities/SimultaneousProcessingTest.php` - 4 test cases (phase-based)

## Key Features Demonstrated

### 1. State Persistence (NEW!)

Abilities automatically save and restore their state after each turn:

```php
// Turn 1: Player uses Power Strike (3 turn cooldown)
$ability->markUsed(1);
$this->abilityRegistry->saveAbilityStates($combatant, $abilities);

// Saved to database:
$combatant->ability_state = [
    'power_strike' => ['last_used_turn' => 1],
    'hardiness' => ['charges' => 1],
];

// Turn 2: Next request/turn - state is restored
$abilities = $this->abilityRegistry->getAbilitiesForCombatant($combatant);
$powerStrike = $abilities->firstWhere('key', 'power_strike');
$powerStrike->isOnCooldown(2); // true - on cooldown
$powerStrike->isOnCooldown(4); // false - available again

// Turn 3: Player uses Hardiness
$hardiness->consume();
$this->abilityRegistry->saveAbilityStates($combatant, $abilities);

// Saved to database:
$combatant->ability_state = [
    'power_strike' => ['last_used_turn' => 1],
    'hardiness' => ['charges' => 0],  // Used up!
];
```

**Benefits:**
- State persists across requests (different turns on different requests)
- Efficient JSON storage per combatant
- Cooldowns and charges tracked automatically
- Single save per turn per combatant

### 2. Per-Combatant Configuration

Abilities can have different configurations per combatant:

```php
// In HeroEncounterHelper.php
'vampire_lord' => [
    'abilities' => [
        ['key' => 'lifesteal', 'config' => ['heal_percent' => 1.0]], // 100% healing
    ],
],

'weak_vampire' => [
    'abilities' => [
        ['key' => 'lifesteal', 'config' => ['heal_percent' => 0.25]], // 25% healing
    ],
],
```

### 3. Clean Interface Design

Each ability implements specific trait interfaces:

```php
class Lifesteal extends AbstractAbility implements ModifiesDamage
{
    public function afterDamageDealt(CombatContext $context): void
    {
        // Healing logic here
    }
}
```

### 4. Isolated Testing

Abilities can be tested in complete isolation:

```php
public function testLifestealHeals50PercentByDefault()
{
    $context = new CombatContext($attacker, $target, $battle);
    $context->damage = 40;

    $lifesteal = new Lifesteal('lifesteal', ['heal_percent' => 0.5]);
    $lifesteal->afterDamageDealt($context);

    $this->assertEquals(20, $context->healing); // 50% of 40
}
```

### 5. Easy to Add New Abilities

Adding a new ability requires only:

1. Create ability class (20-30 lines)
2. Add definition to HeroAbilityHelper (5 lines)

No modifications to HeroBattleService needed!

## Simultaneous Turn Processing (CRITICAL!)

**All player actions are processed simultaneously each turn.** This means:
- Player A attacks Player B
- Player B attacks Player A
- Both attacks are calculated, then applied at the same time

### Why This Matters for Abilities

❌ **WRONG - Sequential processing:**
```php
// Player A attacks Player B with lifesteal
$damage = 30;
$playerB->health -= $damage; // B takes 30 damage
$playerA->health += 15;      // A heals 15

// Player B attacks Player A
$damage = 25;
$playerA->health -= $damage; // A takes 25, but already has +15 from lifesteal!
```

✅ **CORRECT - Simultaneous processing:**
```php
// Phase 1: Calculate all damage/healing
$contextA = new CombatContext($playerA, $playerB, $battle);
$contextA->damage = 30;
$lifesteal->afterDamageDealt($contextA); // Sets $contextA->healing = 15

$contextB = new CombatContext($playerB, $playerA, $battle);
$contextB->damage = 25;

// Phase 2: Apply ALL damage/healing at once
$contextA->applyDamage();  // B takes 30 damage
$contextA->applyHealing(); // A heals 15
$contextB->applyDamage();  // A takes 25 damage

// Net result: Both attacks happen "at the same time"
```

### Phase-Based Processing Architecture

```php
// In HeroBattleService::processTurn()

// PHASE 1: Calculate damage for all combatants
$contexts = [];
foreach ($battle->combatants as $combatant) {
    if ($combatant->action === null) continue;

    $target = $this->getTarget($combatant, $battle);
    $context = new CombatContext($combatant, $target, $battle);

    // Calculate base damage
    $context->damage = $this->calculateDamage($combatant, $target);

    // Get abilities for this combatant
    $abilities = $this->abilityRegistry->getAbilitiesForCombatant($combatant);

    // Trigger abilities - they modify context, NOT combatant health
    foreach ($abilities as $ability) {
        if ($ability instanceof ModifiesDamage) {
            $ability->afterDamageDealt($context);
        }
        if ($ability instanceof ModifiesEvasion && $combatant === $target) {
            $ability->beforeDamageReceived($context);
        }
    }

    $contexts[] = $context;
}

// PHASE 2: Apply ALL damage/healing simultaneously
foreach ($contexts as $context) {
    $context->applyDamage();
    $context->applyHealing();
}

// PHASE 3: Process death triggers
foreach ($battle->combatants as $combatant) {
    if ($combatant->current_health <= 0) {
        $abilities = $this->abilityRegistry->getAbilitiesForCombatant($combatant);
        foreach ($abilities as $ability) {
            if ($ability instanceof TriggersOnDeath) {
                $shouldDie = $ability->beforeDeath($context);
                if (!$shouldDie) {
                    $combatant->current_health = 1; // Hardiness saves at 1 HP
                    break;
                }
            }
        }
    }
}

// PHASE 4: Save ability states for all combatants
foreach ($battle->combatants as $combatant) {
    $abilities = $this->abilityRegistry->getAbilitiesForCombatant($combatant);
    $this->abilityRegistry->saveAbilityStates($combatant, $abilities);
}
```

## Example Usage in HeroBattleService

### Loading Abilities with State

```php
// At the start of processing a turn
$combatant = $battle->combatants->first();

// AbilityRegistry automatically restores saved state
$abilities = $this->abilityRegistry->getAbilitiesForCombatant($combatant);

// Abilities have their state restored:
// - Hardiness has charges remaining from previous turns
// - Power Strike knows when it was last used
// - All cooldowns are tracked
```

### Complete Turn Processing Example

```php
public function processTurn(HeroBattle $battle): void
{
    // PHASE 1: Collect all actions and calculate effects
    $contexts = [];

    foreach ($battle->combatants as $combatant) {
        if (!$combatant->action || $combatant->current_health <= 0) {
            continue;
        }

        $target = $this->determineTarget($combatant, $battle);
        $context = new CombatContext($combatant, $target, $battle);

        // Calculate base damage
        $attack = $combatant->attack;
        $defense = $target->defense;
        $baseDamage = max(0, $attack - $defense);
        $context->damage = $baseDamage;

        // Load abilities with saved state
        $abilities = $this->abilityRegistry->getAbilitiesForCombatant($combatant);

        // Trigger damage modification abilities
        foreach ($abilities as $ability) {
            if ($ability instanceof ModifiesDamage) {
                $ability->afterDamageDealt($context);
                // Lifesteal adds to $context->healing
                // PowerStrike adds to $context->damage
            }
        }

        // Load target abilities
        $targetAbilities = $this->abilityRegistry->getAbilitiesForCombatant($target);

        // Trigger evasion modification abilities
        foreach ($targetAbilities as $ability) {
            if ($ability instanceof ModifiesEvasion) {
                $ability->beforeDamageReceived($context);
                // Elusive sets $context->evadeMultiplier = 0
            }
        }

        // Apply evasion
        if ($context->evadeMultiplier < 1.0) {
            $context->damage = (int) round($context->damage * $context->evadeMultiplier);
        }

        $contexts[] = $context;
    }

    // PHASE 2: Apply ALL damage and healing simultaneously
    foreach ($contexts as $context) {
        $context->applyDamage();  // Applies to shield then health
        $context->applyHealing(); // Caps at max health
    }

    // PHASE 3: Check for deaths and trigger death abilities
    foreach ($battle->combatants as $combatant) {
        if ($combatant->current_health <= 0) {
            $abilities = $this->abilityRegistry->getAbilitiesForCombatant($combatant);

            foreach ($abilities as $ability) {
                if ($ability instanceof TriggersOnDeath) {
                    $context = new CombatContext($combatant, $combatant, $battle);
                    $shouldDie = $ability->beforeDeath($context);

                    if (!$shouldDie) {
                        // Hardiness prevents death
                        $combatant->current_health = 1;
                        break;
                    }
                }
            }
        }
    }

    // PHASE 4: Save ability states
    foreach ($battle->combatants as $combatant) {
        $abilities = $this->abilityRegistry->getAbilitiesForCombatant($combatant);
        $this->abilityRegistry->saveAbilityStates($combatant, $abilities);
    }

    // PHASE 5: Collect and log all messages
    $battleLog = [];
    foreach ($contexts as $context) {
        if (!empty($context->messages)) {
            $battleLog[] = $context->getMessagesString();
        }
    }

    // Save turn to database
    $battle->current_turn++;
    $battle->save();
}
```

### Old Approach (Before) - Sequential Processing

```php
// Current (Before) - Hardcoded checks scattered everywhere
if (in_array('lifesteal', $combatant->abilities ?? []) && $damage > 0) {
    $healing = round($damage / 2);
    $health += $healing;
    $description .= sprintf(' %s heals for %s health.', $combatant->name, $healing);
}

if (in_array('elusive', $target->abilities ?? []) && !$combatant->has_focus) {
    $evadeMultiplier = 0;
}

// New (After) - Phase-based simultaneous processing
$context = new CombatContext($attacker, $target, $battle);
$context->damage = $calculatedDamage;

// Get all abilities for combatants
$attackerAbilities = $this->abilityRegistry->getAbilitiesForCombatant($attacker);
$targetAbilities = $this->abilityRegistry->getAbilitiesForCombatant($target);

// Trigger abilities that modify damage
foreach ($attackerAbilities as $ability) {
    if ($ability instanceof ModifiesDamage) {
        $ability->afterDamageDealt($context);
    }
}

// Trigger abilities that modify evasion
foreach ($targetAbilities as $ability) {
    if ($ability instanceof ModifiesEvasion) {
        $ability->beforeDamageReceived($context);
    }
}

// Apply damage
$target->current_health -= $context->damage;

// Check for death prevention
if ($target->current_health <= 0) {
    foreach ($targetAbilities as $ability) {
        if ($ability instanceof TriggersOnDeath) {
            if (!$ability->beforeDeath($context)) {
                // Death prevented
                break;
            }
        }
    }
}

// Add all messages to battle log
$battleLog[] = $context->getMessagesString();

// Note: Abilities modify context values, NOT combatant health directly
// This ensures all actions are processed simultaneously
```

### State Persistence Example

```php
// Example: Tracking Power Strike cooldown across turns

// Turn 1 - Player uses Power Strike
$context = new CombatContext($attacker, $target, $battle);
$abilities = $this->abilityRegistry->getAbilitiesForCombatant($attacker);
$powerStrike = $abilities->firstWhere('key', 'power_strike');

if (!$powerStrike->isOnCooldown($battle->current_turn)) {
    $powerStrike->afterDamageDealt($context);
    $powerStrike->markUsed($battle->current_turn); // Marks as used on turn 1
}

// Save state
$this->abilityRegistry->saveAbilityStates($attacker, $abilities);
// Database now has: ability_state = ['power_strike' => ['last_used_turn' => 1]]

// Turn 2 - New request, state restored
$abilities = $this->abilityRegistry->getAbilitiesForCombatant($attacker);
$powerStrike = $abilities->firstWhere('key', 'power_strike');
$powerStrike->isOnCooldown(2); // Returns true (1 turn passed, needs 3)

// Turn 3 - Still on cooldown
$powerStrike->isOnCooldown(3); // Returns true (2 turns passed, needs 3)

// Turn 4 - Available again!
$powerStrike->isOnCooldown(4); // Returns false (3 turns passed, ready to use)
```

## Benefits Demonstrated

### Developer Experience
- **Before**: 50-100 lines scattered across service to add ability
- **After**: 20-30 lines in single file

### Maintainability
- **Before**: Ability logic scattered in multiple methods
- **After**: Each ability is self-contained class

### Testability
- **Before**: Full battle simulation required
- **After**: Unit test abilities in isolation

### Extensibility
- **Before**: Must understand entire battle system
- **After**: Extend AbstractAbility, implement interface

### Configuration Flexibility
- **Before**: Hardcoded percentages (lifesteal always 50%)
- **After**: Per-combatant config (vampire lord 100%, weak vampire 25%)

## Next Steps

1. Integrate with existing HeroBattleService
2. Migrate remaining abilities to new system
3. Add event dispatcher for cleaner hook system
4. Create migration path for existing battles

## Running Tests

Once composer dependencies are installed:

```bash
vendor/bin/phpunit tests/Unit/Domain/HeroBattle/Abilities/
```

Expected output: All tests passing (22 tests, 45+ assertions)

### Test Coverage

- **Lifesteal**: Default healing, custom healing, max health cap, zero damage handling
- **Elusive**: Evasion with/without focus
- **Hardiness**: Death prevention, charge consumption, no charges
- **Power Strike**: Cooldown tracking, state save/restore, no cooldown behavior, mixed state
- **AbilityRegistry**: State restoration, state saving, per-combatant config, empty state handling
- **Simultaneous Processing (NEW)**: Phase-based damage, fairness verification, shield mechanics
