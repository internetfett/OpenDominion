# Deep Research into `assignRealms` Functionality

## Overview

The `assignRealms` functionality is implemented in the `RealmAssignmentService` class (/data/data/com.termux/files/home/OpenDominion/src/Services/RealmAssignmentService.php:477) and is responsible for distributing registered players across realms in a balanced and fair manner for each game round.

## Core Architecture

### Key Classes

1. **`RealmAssignmentService`** - Main orchestrator class containing all assignment logic
2. **`Player`** - Non-persisted model representing a player during assignment with playstyle affinities and favorability data
3. **`PlaceholderPack`** - Non-persisted model representing groups of players registering together
4. **`PlaceholderRealm`** - Non-persisted model representing realms during the assignment process

### Database Integration

- **`Realm`** - Persistent model for game realms
- **`Pack`** - Persistent model for player groups
- **`Dominion`** - Player's game state
- **`User`** - Player accounts with ratings and preferences
- **`UserFeedback`** - Favorability matrix between players

## Algorithm Flow

### Main Assignment Process (`assignRealms` method at line 477)

1. **Pack Closure** (line 480): Close all pack registrations and dissolve single-member packs
2. **Data Loading** (lines 483-486): Load players, calculate Discord preferences, create pack objects
3. **Discord Separation** (line 489): Separate non-Discord players into dedicated realms
4. **Target Calculation** (lines 492-495): Calculate optimal realm count and target metrics
5. **Large Pack Assignment** (line 498): Create initial realms from large packs (>3 members)
6. **Small Pack Assignment** (line 501): Assign remaining packs to existing realms
7. **Solo Player Distribution** (line 504): Distribute individual players across realms
8. **Optimization** (line 507): Iterative improvement through player swapping
9. **Realm Creation** (line 515): Persist assignments to database
10. **Notifications** (line 518): Notify all players of their assignments

### Scoring System

The algorithm uses a sophisticated multi-factor scoring system:

**Compatibility Score** (line 293):
- **Favorability Matrix**: Player endorsement/conflict data (-100 penalty for severe conflicts)
- **Playstyle Balance**: Measures deviation from ideal composition (attackers: 50, converters: 30, explorers: 50, ops: 30)

**Balance Score** (line 832):
- **Rating Balance**: Encourages equal average ratings across realms
- **Improvement Reward**: 2x multiplier for moves that improve balance
- **Degradation Penalty**: 3x penalty for moves that worsen balance

**Size Management** (line 911):
- **Hard Limit Enforcement**: -5000 penalty for exceeding target size
- **Distribution Encouragement**: Bonuses for undersized realms

### Optimization Process

**Pre-balancing** (line 1055): Simple size balancing by moving solo players

**Iterative Optimization** (line 1063):
- **Sampling Approach**: Tests 25 random player pairs per iteration
- **Multi-factor Evaluation**: Uses full scoring system for swap decisions
- **Convergence Detection**: Stops when no beneficial swaps found
- **Performance Limits**: Maximum 50 iterations with early termination

## Configuration Constants

```php
MAX_PACKS_PER_REALM = 3              // Maximum packs in one realm
MAX_PACKED_PLAYERS_PER_REALM = 8     // Maximum packed players per realm
ASSIGNMENT_HOURS_BEFORE_START = 96   // Hours before round start to assign
ASSIGNMENT_MIN_REALM_COUNT = 8       // Minimum realms to create
ASSIGNMENT_MAX_REALM_COUNT = 14      // Maximum realms to create
```

## Individual Player Assignment (`findRealm` method at line 1383)

For late-joining players after assignment is complete:

1. **Pre-assignment Check**: Uses realm 0 if assignment hasn't started
2. **Candidate Filtering**: Gets top 3 smallest Discord-enabled realms with alignment matching
3. **Player Creation**: Builds favorability data only for candidate realm members
4. **Dynamic Scoring**: Uses same compatibility + balance scoring with 2x balance weight
5. **Best Match Selection**: Returns highest-scoring realm

## Key Features

### Discord Integration
- Separate realms for Discord and non-Discord players
- Discord preference stored in realm settings
- Automatic separation prevents mixing preferences

### Pack Management
- Large packs (>3 members) become realm foundations
- Pack integrity maintained (all members in same realm)
- Dynamic pack upgrading/downgrading based on realm count needs

### Playstyle Balancing
- Uses configured ideal composition ratios
- Measures deviation from ideal averages
- Rewards moves toward better balance

### Conflict Avoidance
- Heavy penalties for negative favorability relationships
- Prevents assignment of conflicting players to same realm

### Fair Distribution
- New players distributed round-robin for equality
- Experienced players use full scoring system
- Size constraints ensure balanced realm populations

## Testing Coverage

The system includes comprehensive unit tests (RealmAssignmentServiceTest.php) that validate:

- **100-player simulation** with realistic data distributions
- **Pack integrity** and constraint enforcement
- **Balance metrics** including size and rating variance
- **Conflict avoidance** through favorability checking
- **Performance requirements** (completes within 5 seconds)
- **Playstyle distribution** analysis
- **Integration testing** for individual player assignment

## Possible Enhancements

### 1. **Dynamic Playstyle Composition**
- **Current**: Fixed ideal ratios (50/30/50/30)
- **Enhancement**: Adapt ideals based on round type, player pool composition, or historical data
- **Impact**: Better meta-adaptation and more contextual balance

### 2. **Machine Learning Integration**
- **Current**: Rule-based scoring with fixed weights
- **Enhancement**: ML model trained on historical assignment satisfaction data
- **Impact**: Improved assignment quality based on actual player experience

### 3. **Multi-Objective Optimization**
- **Current**: Single weighted score combining all factors
- **Enhancement**: Pareto optimization for competing objectives (balance vs compatibility vs performance)
- **Impact**: Better exploration of trade-offs between different assignment goals

### 4. **Advanced Pack Relationship Modeling**
- **Current**: Binary pack compatibility based on member favorability
- **Enhancement**: Pack-level relationships, alliance networks, historical collaboration data
- **Impact**: More nuanced pack placement considering group dynamics

### 5. **Temporal Favorability Weighting**
- **Current**: All favorability data weighted equally
- **Enhancement**: Time-decay for old relationships, stronger weight for recent interactions
- **Impact**: More relevant conflict avoidance based on current relationships

### 6. **Skill-Based Matchmaking Integration**
- **Current**: Simple rating averaging for realm strength
- **Enhancement**: Consider skill categories (military, magic, espionage), role preferences
- **Impact**: More strategic realm compositions with complementary skill sets

### 7. **Geographic/Timezone Optimization**
- **Current**: No geographic consideration
- **Enhancement**: Cluster players by timezone/region for better coordination
- **Impact**: Improved realm activity synchronization and communication

### 8. **Hierarchical Assignment Algorithm**
- **Current**: Flat assignment process
- **Enhancement**: Multi-stage process with coarse-then-fine optimization
- **Impact**: Better scalability for larger player pools

### 9. **Adaptive Realm Count Calculation**
- **Current**: Based primarily on large pack count
- **Enhancement**: Consider player density preferences, historical optimal sizes, queue times
- **Impact**: More responsive realm sizing to actual player behavior

### 10. **Real-time Assignment Monitoring**
- **Current**: Batch assignment with statistics at end
- **Enhancement**: Real-time metrics dashboard, assignment preview, manual override capability
- **Impact**: Better administrative control and transparency

### 11. **Player Preference System**
- **Current**: Only Discord preference considered
- **Enhancement**: Realm size preference, playstyle matching preference, competitive level
- **Impact**: More personalized assignments matching player expectations

### 12. **Assignment Fairness Auditing**
- **Current**: Basic statistics generation
- **Enhancement**: Bias detection, fairness metrics across different player demographics
- **Impact**: Ensuring equitable treatment across all player types

### 13. **Performance Optimization**
- **Current**: O(n²) operations in optimization phase
- **Enhancement**: Spatial data structures, approximation algorithms, parallel processing
- **Impact**: Support for larger player pools with maintained quality

### 14. **Alternative Assignment Strategies**
- **Current**: Single algorithm approach
- **Enhancement**: Multiple algorithm variants (genetic algorithm, simulated annealing, constraint programming)
- **Impact**: A/B testing capabilities and situation-specific optimization

### 15. **Historical Data Integration**
- **Current**: Only current round favorability considered
- **Enhancement**: Multi-round success metrics, player satisfaction tracking, long-term relationship patterns
- **Impact**: Learning from past assignments to improve future ones

Each enhancement offers different benefits in terms of assignment quality, player satisfaction, administrative control, and system scalability. The current implementation provides a solid foundation that balances multiple competing objectives while maintaining reasonable performance characteristics.