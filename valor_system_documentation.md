# OpenDominion Valor System Documentation

## Overview

The Valor system in OpenDominion is a prestige/scoring mechanism that rewards players for various achievements throughout a round. Valor is calculated for both individual dominions and entire realms, providing a measure of accomplishment and contribution to the game.

## Database Schema

### Valor Table (`valor`)
The `valor` table tracks individual valor awards given to dominions:

- `id` - Primary key
- `round_id` - Round where valor was earned
- `realm_id` - Realm of the dominion that earned valor
- `dominion_id` - Dominion that earned the valor
- `source` - Source/reason for the valor award (string)
- `amount` - Amount of valor awarded (float)
- `created_at`, `updated_at` - Timestamps

### Dominion Table Addition
- `valor` - Current total valor for the dominion (unsigned integer, default 0)

### Realm Table Addition  
- `valor` - Current total valor for the realm (unsigned integer, default 0)

### Round Table Addition
- `largest_hit` - Tracks the largest invasion hit in the round (used for valor calculations)

## Valor Calculation Components

Valor is calculated from two main components:

### 1. Fixed Valor
Fixed valor is distributed based on established metrics and rankings, with fixed pools distributed proportionally:

#### Land Rank Valor (Pool: 6,000)
- **1st place**: 1,000 valor
- **2nd place**: 500 valor  
- **3rd+ place**: Formula: `max(0, round(1250 / rank) - 5)`

#### Total Land Valor (Pool: 3,000)
- Distributed proportionally based on total land owned
- Formula: `(3000 / totalLandAcrossAllDominions) * dominionLand`

#### Land Conquered Valor (Pool: 1,500)
- Distributed proportionally based on land conquered through invasions
- Formula: `(1500 / totalLandConquered) * dominionLandConquered`

#### Bounties Valor (Pool: 1,500)
- Distributed proportionally based on bounties collected
- Formula: `(1500 / totalBounties) * dominionBounties`

### 2. Bonus Valor
Bonus valor is awarded for specific actions during the round:

#### War Hits
- **Amount**: 10 valor per successful invasion during war
- **Trigger**: Successful invasion when realms are at war
- **Code location**: `/src/Services/Dominion/Actions/InvadeActionService.php:304-306`

#### Largest Hit (Hero of the Round)
- **Base Amount**: 5 valor + (days in round × 0.4)
- **Trigger**: When a dominion achieves the largest single invasion in the round
- **Requirements**: Round must be more than 1 day old
- **Code location**: `/src/Services/Dominion/Actions/InvadeActionService.php:817-820`

#### Wonder Attacks
- **Enemy Wonder**: 25 × damage contribution
- **Neutral Wonder**: 10 × damage contribution  
- **Trigger**: Participating in wonder destruction
- **Code location**: `/src/Services/Dominion/Actions/WonderActionService.php:515-521`

## Key Classes and Files

### Models
- `/src/Models/Valor.php` - Valor record model with relationships to Round, Realm, and Dominion
- `/src/Models/Dominion.php` - Contains `valor` field (line ~500)
- `/src/Models/Realm.php` - Contains `valor` field  
- `/src/Models/Round.php` - Contains `largest_hit` field

### Calculators
- `/src/Calculators/ValorCalculator.php` - Main calculation logic
  - `calculate()` - Master calculation method combining fixed and bonus valor
  - `calculateFixedValor()` - Calculates valor from rankings and statistics
  - `calculateBonusValor()` - Sums up bonus valor from valor table records

### Services
- `/src/Services/ValorService.php` - Valor management service
  - `awardValor()` - Creates valor records for specific actions
  - `updateValor()` - Updates dominion and realm valor totals (called during tick)

### Integration Points
- `/src/Services/Dominion/TickService.php` - Updates valor during hourly game tick
- `/src/Services/Dominion/Actions/InvadeActionService.php` - Awards valor for invasions
- `/src/Services/Dominion/Actions/WonderActionService.php` - Awards valor for wonder participation

## Valor Award Sources

The system recognizes these valor sources in `ValorService::awardValor()`:

1. **`largest_hit`** - Hero of the Round achievement
2. **`war_hit`** - Successful invasion during war
3. **`wonder`** - Damage to enemy realm wonder
4. **`wonder_neutral`** - Damage to neutral wonder

## Calculation Process

### During Game Tick (`TickService`)
1. `ValorService::updateValor()` is called
2. `ValorCalculator::calculate()` computes current valor for all dominions/realms
3. Fixed valor calculated from daily rankings (land, bounties, conquests)
4. Bonus valor summed from valor table records
5. Dominion and realm tables updated with new totals

### During Game Actions
- Invasion actions check for war status and largest hit, awarding bonus valor
- Wonder actions award valor based on damage contribution
- Records created in valor table via `ValorService::awardValor()`

## Display and UI

Valor is displayed in several locations:
- **Realm page**: Shows total realm valor if > 0 (`/app/resources/views/pages/dominion/realm.blade.php:171-173`)
- **World page**: Likely shows valor rankings
- **Hero tournaments page**: May relate to largest hit valor

## Constants and Tuning

### Fixed Valor Pools (in `ValorCalculator`)
```php
FIXED_VALOR_LAND_RANK = 6000       // Land ranking pool
FIXED_VALOR_LAND_TOTAL = 3000      // Total land pool  
FIXED_VALOR_LAND_CONQUERED = 1500  // Land conquered pool
FIXED_VALOR_BOUNTIES = 1500        // Bounties pool
```

### Bonus Valor Amounts (in `ValorService`)
```php
BONUS_VALOR_HOTR_BASE = 5              // Base hero of round valor
BONUS_VALOR_HOTR_DAY_MULTIPLIER = 0.4  // Daily multiplier for HOTR
BONUS_VALOR_WAR_HIT = 10               // War invasion valor
BONUS_VALOR_WONDER = 25                // Enemy wonder multiplier
BONUS_VALOR_WONDER_NEUTRAL = 10        // Neutral wonder multiplier
```

## Technical Notes

### Relationships
The Valor model has somewhat incorrect relationships (should use `belongsTo` instead of `hasOne`) but functionally works for the current use case.

### Performance
- Valor calculations run during every game tick for all active dominions
- Fixed valor requires querying daily rankings and statistics
- Bonus valor queries the valor table for each dominion

### Data Integrity
- Valor records are created immediately when actions occur
- Total valor fields are recalculated and updated during each tick
- Uses Laravel's `upsert()` method for efficient bulk updates

## Migration History

1. **2024-09-27**: Created `valor` table
2. **2024-10-05**: Added `valor` columns to `dominions` and `realms` tables, `largest_hit` to `rounds`
3. **2024-10-14**: Added `racial_value` field after valor
4. **2025-02-26**: Added `chaos` field after valor

This system provides a comprehensive way to track and reward player achievements, encouraging both individual excellence and collaborative realm activities.