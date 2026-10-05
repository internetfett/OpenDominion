# Hero Combat Engine

Hero battles run on a self-contained, class-based engine in `src/HeroCombat/`. It handles
simultaneous turns, N-vs-N teams (co-op PvE, team PvP, NPC allies), duration and stack based
effects, ally targeting and AoE, cooldowns and charges, and deterministic replays.

The design borrows from Pokémon Showdown (simultaneous choice, priority ordering, ordered
event handlers, side/field effects, seeded replays), Unreal's Gameplay Ability System
(abilities + effects + modifiers, tag gating), and RPG Maker (state expiry timing).

## Module map

```
src/HeroCombat/
  Contracts/        Ability, Effect, AiStrategy, EnemyTemplate, Encounter, CombatantSpawner
  Engine/           Pure rules. No Eloquent, no Laravel.
    Battle.php          runtime facade passed to everything (state + services)
    BattleState.php     turn, seed, combatants, team/field effects, intents
    CombatantState.php  one combatant: base stats, health, abilities, effects, cooldowns, charges, queue
    TurnResolver.php    resolves one turn (see pipeline)
    BattleEngine.php    advance() loops turns while everyone is ready
    IntentDecider.php   forced → queued → AI
    ActionValidator.php cooldowns, charges, tag gating, canUse; queue projection
    ActionContext.php   what abilities receive: actor, targets, helpers
    Damage/             DamageRequest → DamageResolver → DamageResult (one pipeline for every hit)
    Effects/            EffectInstance, EffectManager (apply/stack/tick/remove), EffectDispatcher (ordered hooks)
    Stats/              Stat enum, Modifier, StatCalculator
    Targeting/          TargetRule enum, TargetResolver
    Events/             BattleLog, LogEntry, BattleEvent (structured), EventType
    Random/             RandomSource, SeededRandomSource
    Victory/            VictoryCondition, LastTeamStanding
  Content/          Everything game-specific. Add new content here.
    Abilities/          Attack, Defend, Focus, Counter, Recover, class abilities, Boss/ signature moves
    Effects/            Stances/, Passives/, Boss/ (telegraphs, phase cycles), buffs/debuffs
    Ai/WeightedStrategy presets (balanced, aggressive, defensive, pirate, …)
    Enemies/            EnemyTemplate classes
    Encounters/         Encounter classes (rosters, grants, scaling, victory rules)
    Loadouts/HeroClassLoadouts  class → abilities/passives (disabled by default: ENABLED = false)
  Registry/CombatRegistry  explicit key → definition maps
  Persistence/      BattleRepository (models ⇄ state, row lock), CombatantFactory, EloquentCombatantSpawner,
                    StateSnapshot, BattleReplayer
  Presentation/     BattlePresenter / BattleView (view model for Blade)
src/Providers/HeroCombatServiceProvider.php   registers all content (explicit lists)
src/Services/Dominion/HeroBattleService.php   app entry point: create battles, matchmaking, processTurn
src/Services/Dominion/HeroBattleOutcomeService.php   records, Elo, tournaments, raid contributions
```

The engine never touches models. `HeroBattleService` loads a `Battle` through
`BattleRepository`, runs `BattleEngine`, and persists the result inside one transaction holding a
`lockForUpdate` on the `hero_battles` row, so a tick and several players can't resolve the same
turn twice.

## Turn pipeline

```
processTurn(HeroBattle)
 └─ DB::transaction: lock row → load Battle
     └─ BattleEngine::advance — while not finished and every living human is ready:
         reseed RNG (seed + turn)
         TurnResolver::resolve
           1. TurnStart hooks (every effect instance)
           2. Intents     each living combatant: forcedIntent hook → queued action → AI strategy
           3. Declare     Ability::onDeclare for every intent (stances are applied here)
           4. Order       priority desc, then combatant id
           5. Resolve     per intent: resolve targets → Ability::resolve → start cooldown, spend charge
                          → AbilityUsed hooks → OwnerActionEnd expiry → process deaths
           6. TurnEnd hooks → tick TurnEnd durations → Encounter::onTurnEnd → process deaths
           7. VictoryCondition → finished / winning team, else turn++
     └─ persist state + log rows → if finished: HeroBattleOutcomeService::finalize
```

- **Simultaneous semantics.** A combatant downed earlier in the turn still acts. Anyone summoned mid-turn acts from the next turn.
- **Readiness.** NPCs are always ready. A human is ready when they have a queued action or are automated. `checkTime()` drains time banks and switches anyone at zero to automated.
- **Death processing** repeats until stable: `onDeath` runs on the dying combatant's own effects, then `onAnyDeath` on every other effect. A death trigger can kill, summon or revive.

## State and persistence

| Table | Engine field |
|---|---|
| `hero_battles.current_turn / seed / mode / encounter_key` | `BattleState` |
| `hero_battles.effects` | `{team: {n: [instances]}, field: [instances]}` |
| `hero_battles.winning_team` | source of truth for results; `winner_combatant_id` is kept as a representative (first human on the winning team) for older views |
| `hero_battles.initial_state` | `StateSnapshot` JSON taken at creation, for replays |
| `hero_combatants.team / template_key` | team number; enemy template key (null for heroes) |
| `hero_combatants.controller_dominion_id` | dominion piloting the combatant (`dominion_id` is the owner) |
| `hero_combatants.health … recover` | base stats (`health` = max health). Effects never mutate these |
| `hero_combatants.abilities` | active ability keys the combatant can choose |
| `hero_combatants.effects / cooldowns / charges` | effect instances; ability → turn it's usable again; ability → charges left |
| `hero_combatants.actions` | queued intents `[{ability, target}]` (max 6) |
| `hero_combatants.strategy` | AI strategy key (`CombatantState::$ai`) |
| `hero_battle_actions` | one row per resolved action or status line: `description` (rendered text) + `events` (structured) |

An effect instance is serialized as `{key, src, turns, stacks, data, turn, seq}`. `seq` is the
global apply order, which the dispatcher uses to break ties.

## Effects

Buffs, debuffs, statuses **and passives** are all `Effect` classes. Passives are effects of kind
`Innate`: permanent and not dispellable. Definitions are stateless singletons; per-application
state lives on the `EffectInstance` (stacks, remaining turns, `data`).

- **Scopes.** An effect lives on a combatant (`EffectManager::apply`), a team (`applyToTeam`) or the battlefield (`applyToField`). Team and field instances apply to whoever `Effect::appliesTo()` accepts. The default excludes ids listed in `data.exclude`.
- **Stacking.** `Refresh` resets the duration and merges data. `Stack` adds stacks up to `maxStacks()`, which may depend on the owner (Focused stacks without limit under Channeling). `Replace` swaps in a new instance. `Independent` keeps every application.
- **Expiry timing.** `TurnEnd` (default), `OwnerActionEnd`, `OnDamageTaken`, or `OnConsume` (removed only explicitly). An effect applied *during* end-of-turn ticking is not ticked in that same pass, which is how Freezing hands over to Frozen for exactly the next turn.
- **Tags** (`CombatTag`). Effects grant tags. Abilities declare `blockedByTags()` (default: Stunned) and `requiredTags()`. Effects can declare `immuneToTags()`. Cleanse removes `Curse`-tagged effects. Stances are queried by tag (`Defending`, `Countering`).
- **Modifiers.** `effective = round((base + Σflat) × (1 + Σpercent))`, then overrides (e.g. Frozen → 0), clamped at 0. A modifier must not compute other *effective* stats inside `modifiers()`, because that recurses. Use `baseStat()` (see Mending).

### Hook reference (all no-op in `AbstractEffect`)

| Hook | Runs for | Typical use |
|---|---|---|
| `onApply` / `onExpire` / `onRemove` | the instance | setup; hand-offs (Freezing→Frozen); `onRemove` covers dispels too |
| `onTurnStart` / `onTurnEnd` | every instance in the battle | telegraph rolls, phase changes, regen/DoT |
| `forcedIntent` | the acting combatant's effects | telegraphed moves, charged attacks |
| `beforeDamageDealt` | attacker's effects | add bonus damage / defend modifier (Crushing Blow) |
| `redirectDamage` | target's effects | Cover |
| `onEvaded` | target's effects | Elusive sets `evadeMultiplier = 0` |
| `beforeDamageTaken` | target's effects | Shield absorbs (priority 10) |
| `onLethalDamage` → bool | target's effects | Hardiness (priority -10, after shields) |
| `afterDamageTaken` / `afterDamageDealt` | target / attacker | Lifesteal, Frostbite stacks |
| `onAbilityDeclared` | actor's own effects, right after `Ability::onDeclare` | swap or adjust stances (Shadow Dance, Rejuvenation) |
| `onAbilityUsed` | actor's effects | consume Focused after an Attack-tagged ability; Form openers; Battle Fury streaks |
| `adjustCooldown` → int | holder's effects, when a cooldown starts | Riposte, Attunement |
| `onDeath` | the dead combatant's own effects | PowerSource, PhaseCycle cleanup |
| `onAnyDeath` | every other effect | soul tribute / harvest style mechanics |

**Ordering.** `EffectDispatcher` sorts handlers by `handlerPriority($hook)` (high first), then
by apply sequence. "Runs for X's effects" includes the team and field instances that apply to X.

## Damage pipeline (`DamageResolver::resolve`)

1. `beforeDamageDealt` (attacker), then `redirectDamage` (target)
2. Raw damage: `flatDamage × multiplier × hits` if flat, else `max(0, power − defense) × multiplier × hits`, where
   - power = Attack (+ Counter if this is a counter-attack, otherwise + Focus × stacks if Focused) + bonusDamage
   - defense = effective Defense (Defending doubles it via a +100% modifier) + `defendModifier` if Defending, or 0 with `ignoreDefense`
3. Evasion (if `canEvade`): roll `int(0,100) < Evasion` → ×0.5, then `onEvaded` hooks
4. `beforeDamageTaken` (shields; skipped by `ignoreShield`)
5. Lethal save via `onLethalDamage` unless `bypassLethalSave`
6. Apply → `OnDamageTaken` expiry → `afterDamageTaken` / `afterDamageDealt`
7. Counter-attack if the target holds `Countering` and the hit is counterable: attack + counter vs defense, no evasion, not shielded, not itself counterable, × hits

`ActionContext::attack()` builds a standard request. `ActionContext::flatDamage()` builds fixed
damage that ignores defense, evasion and counters (boss moves).

## Abilities, AI, targeting

- **Abilities** implement `key/name/description/resolve`. `AbstractAbility` supplies defaults: SingleEnemy, priority 0, no cooldown, unlimited charges. `cooldown()` returns the turns to wait after use; 1 means "not twice in a row" (Focus, Counter, Recover, class abilities). Both `cooldown()` and `maxCharges()` receive the actor, so they can vary.
- **Queue validation** (`ActionValidator::canQueue`) projects cooldowns and charges forward through the actions already queued. At resolution, an invalid queued action falls back to AI.
- **Target rules**: Self, SingleEnemy, SingleAlly, SingleAny, AllEnemies, AllAllies, AllOthers, RandomEnemy. A dead single-enemy target is replaced with a random living enemy; dead ally targets fizzle. `Provoked` forces single-enemy targeting onto the provoker. `SingleAlly` with no target chosen means yourself (`TargetRule::defaultsToSelf()`).
- **Defend and Recover take an ally target.** Defend on an ally is a guard: the defender takes the `defending` stance and puts `covered` on the ally, so hits aimed at the ally strike the defender. Redirects happen once per hit, so mutual guards can't loop. Recover heals the target with the caster's recover stat; the `recovering` −5 defense stays on the caster. In 1v1 the only ally is yourself, so PvP plays exactly as before.
- **Cooldowns can be adjusted by effects** (`Effect::adjustCooldown`, applied by `ActionValidator::cooldownFor`). Riposte uses this to remove Counter's cooldown, Attunement to remove Focus's.
- **AI**: `WeightedStrategy` picks a usable ability by weight. It drops Fortify while shielded, drops Recover when it would overheal, and doubles Recover at ≤40 HP. For ally-targeted abilities, healing goes to the most wounded ally; Defend stays on yourself unless a partner is at ≤40 HP. Player-selectable strategies are flagged `playerSelectable`.
- **Pass** is chosen when nothing is usable (e.g. while stunned).

## Techniques and gear (hero upgrades)

Hero upgrades follow a level schedule (`HeroCalculator::getUpgradeSlots()`, data-driven from `heroes.yml`). A slot exists for every level that has at least one active, slot-consuming upgrade available to the hero's class; directives are free picks. One pick per level.

| Level | Pick | Notes |
|---|---|---|
| 0 | Scion directives/effects | class-gated |
| 1 | Doctrine | |
| 2, 4, 6 (8) | Dominion upgrades (spellbooks, items) | **no combat stats** |
| 3 | Discipline (`type: discipline`) | one per hero |
| 5, 9 | Gear (`type: gear`) | the only upgrades with `combat_*` perks |
| 7 | Form (`type: form`) | |

**Disciplines** and **Forms** have no perks. Their upgrade key is the key of an innate effect in `Content/Effects/Techniques/`, which `CombatantFactory::addHero()` applies to the hero's combatant (`CombatantFactory::TECHNIQUE_TYPES`). They work in every mode, PvP included, because they change how basic actions behave rather than adding buttons.

| Discipline | Rule (constants on the class) |
|---|---|
| `overpower` | attacks against a Defending target ignore 5 defense |
| `battle_fury` | each consecutive Attack: +5 attack, −5 defense (max 3 stacks); any other action resets it |
| `attunement` | Focus has no cooldown and stacks to 2 |
| `rejuvenation` | Recover heals 50% now and 50% at the end of the next turn (`rejuvenating`), with no defense penalty |
| `riposte` | Counter has no cooldown |
| `shadow_dance` | Defend also grants +30 evasion (`shadow_dance_stance`); evaded unfocused hits deal 0 and grant Focus |

**Forms** extend `AbstractForm`: a hidden innate trigger with an `opener()` ability. When the opener resolves, it applies a visible status named after the Form (`<key>_status`, a `FormStatus` built by the provider for each Form) that grants `statusModifiers()` for exactly the following turn (applied with duration 2; inactive on the turn it was applied).

| Form | After | Next turn |
|---|---|---|
| `shield_bash` | Defend | +5 attack |
| `second_wind` | Recover | +5 defense |
| `spellweave` | Focus | +5 counter |
| `arcane_ward` | Focus | +5 defense |
| `ambush` | Counter | +5 attack |
| `feint` | Attack | +10 evasion |

**Gear** is plain `combat_*` perks summed by `HeroCalculator::getHeroCombatStats()`.

Upgrade kinds are shown only in icon tooltips on the heroes page; the table's Combat column shows the technique's effect description (`HeroHelper::getCombatUpgradeDescription()`).

## Parties and borrowed heroes

- `Encounter::maxPartySize()` (default 1). Above 1, a pilot may bring realmmates' heroes. Party-enabled encounters are designed for the party size: there's no automatic scaling.
- `hero_combatants.controller_dominion_id` is who pilots a combatant; `dominion_id` stays the owner. `CombatantState::$controllerDominionId` carries it through the engine.
- `HeroBattleService::createEncounterBattle(..., $companions)` / `createPracticeBattle(..., $companions)` validate party size, same realm and round, and no duplicates. `availableCompanions()` lists every other hero in the realm. Any number of realmmates can borrow the same hero at once (each battle has its own combatant row).
- Everything player-facing goes through the controller: queueing and strategy changes (`HeroCombatant::isControlledBy()`), "battle in progress" checks (`hasBattleInProgress()`), the battle list (`HeroBattle::scopeControlledBy()`), raid objective completion (`HeroBattle::hasWinningController()`). Battles where your hero was borrowed don't block you or appear as yours.
- Raid wins credit one contribution per controller dominion. Owners of borrowed heroes get nothing for now.
- UI: the raid hero tactic form and the practice page show a "Bring a realmmate's hero" select only when the encounter's `maxPartySize() > 1`. The battle page renders one control panel per living combatant you pilot (`BattleView::commandable()`), marked "(borrowed)".

## Daily PvP experience

`HeroBattleOutcomeService::grantDailyExperience()`: when a PvP battle (including tournaments) finishes, each hero whose combatant still has time bank left gains, once per round day (`Round::daysInRound()` of `last_pvp_reward_at` differs from today's, so days roll over at the round's start time), a flat `DAILY_PVP_EXPERIENCE` (25, no experience multipliers), win or lose. Tracked in `heroes.last_pvp_reward_at`; a status line is added to the battle log.

## Analysis: Tactician and the simulator (offline only)

`src/HeroCombat/Analysis/` holds tools for studying 1v1 PvP balance. **Nothing here is registered in the game**; `BattleSimulator` builds its own registry copy.

- `TurnPreview` copies a battle through `StateSnapshot` (no queues or intents, so nothing private leaks), resolves one turn with forced actions, and enumerates every evasion outcome via `Battle::$evasionRoll`, weighting each by probability. It returns a `TurnOutcome` (expected health, attack potential, death chance per combatant), cached for the current turn.
- `MatrixGameSolver` solves a zero-sum matrix game exactly (simplex on the column player's LP; the row mix comes from the duals).
- `TacticianStrategy` (`tactician:balanced|aggressive|defensive`, from `TacticianProfile`) previews every pairing of both sides' legal basic actions, scores outcomes (damage dealt/taken, healing, change in attack potential, win/loss chance), solves for a mixed strategy and samples it. `temperature` blends toward uniform play to make it weaker.
- `BuildFactory` parses specs like `level=9;discipline=riposte;form=feint;gear=greatsword,tower_shield` straight from `heroes.yml`. `BattleSimulator::run()` plays seeded battles (alternating combatant ids); `balancedScore()` also plays the swapped matchup on the same seeds to cancel seed luck.
- Command: `php artisan hero-combat:simulate --a=<spec> --b=<spec> [--strategy-a=tactician:balanced] [--strategy-b=balanced] [--temperature=0.2] [--battles=200] [--seed=1]`, or `--grid=disciplines|forms|gear [--level=9]` for a round-robin score matrix. Expect about 0.1s per Tactician battle.

## Encounters and enemies

An `EnemyTemplate` gives stats, active abilities (`AbstractEnemy` adds the five basics), innate
effects with data, and an AI key. An `Encounter` gives a roster (template, display name, stat and
effect overrides) built from an `EncounterContext` (raid `priorWins`, the leader's stats),
optional `playerGrants()` (class-conditional abilities), a `VictoryCondition`, an `onTurnEnd`
script hook, and an optional victory line. Keys must match the `encounter` attribute raids use on
their `RaidObjectiveTactic` rows.

All 18 original encounters are ported:

| Key | Mechanics |
|---|---|
| `default` (Evil Twin) | copies the leader's stats |
| `rabid_bunny`, `dragonkin`, `gate_warden`, `rebel_corsair`, `rebel_admiral` | stat blocks; Blade Flurry, Enrage |
| `fallen_kings` | `undying`: revive at half health 5 turns after falling |
| `eternal_guardian` | `undying_legion` (999 defense while minions live), `necromancy` cadence → `summon_skeleton` |
| `nightbringer` | `nightfall` cadence → `darkness` (stacking `shrouded` evasion); cultists' `dying_light` strips it |
| `lich_king` | `tome_of_power` phase cycle, `power_source` → `severed` |
| `planewalker_golems`, `planewalker` | golems: Fortify + Hardiness; `void_rift` cadence (interval scales with realm wins), `wounded_retreat`, class grants (Shadow Strike / Great Flood / Demolish), victory line |
| `wraith` | `soul_rend_charge` → forced `soul_rend` (+85 damage, +70 defense if defending) |
| `dreadsoul_skullkeeper` | orcs' `soul_tribute` → `empowered`; health scales with realm wins |
| `dream_of_thessadrash` | `aspect_shift` chain: Veil → Shadow → Maw |
| `heart_of_ice` | `snow_witch_curse` telegraph, `frostbite`, Freezing → Frozen |
| `admiral_varos` | `admirals_orders` telegraph, capped summons |
| `rex_lunae` | `hungering_moon_curse` telegraph → `hungering_moon` (`moonstruck` max-health loss, turns then converts); Cleanse granted |

The old unused "story" encounters (wolf, bandit, imp, cultist) were never reachable and were
not ported.

## Recipes

**Add an ability**
1. Create `Content/Abilities/MyAbility.php` extending `AbstractAbility` (or `AbstractAttack` for weapon hits; override `damageOptions()`).
2. Implement `resolve(ActionContext $context)` using `$context->attack()`, `flatDamage()`, `heal()`, `applyEffect()`, `summon()`, `chose($target, 'defend')` and `say('messageKey', [...])`.
3. Add it to `HeroCombatServiceProvider::ABILITIES`, then grant it through a template, a loadout or `Encounter::playerGrants()`.

**Add a buff or debuff**: extend `AbstractEffect`. Set `kind()`, `stacking()`, `defaultDuration()` and `tags()`, and return modifiers or override hooks. Register it in `EFFECTS` and apply it with `$context->applyEffect($target, 'key', $turns, $stacks, $data)`.

**Add a passive**: extend `AbstractPassive` (or `AbstractStatPassive` for flat stat bonuses, optionally gated to low health). Give it to an enemy through `EnemyTemplate::effects()`, or to a class through `HeroClassLoadouts`.

**Add a telegraphed boss move set**: extend `Effects/Boss/AbstractTelegraph`, list `moves()` and `tells()` (tells must not name the counter-play), and add the move abilities extending `Abilities/Boss/AbstractBossMove`. Put the telegraph in the boss template's `effects()`.

**Add a scheduled boss action** (summon every N turns, recurring buff): extend `Effects/Boss/AbstractCadence` with `abilityKey()`, `defaultInterval()`, `warning()` and optionally `shouldFire()`. It fires on turns 1, 1+N, …, warns at the end of the turn before, and accepts `data.interval` for scaling. Summons can extend `Abilities/Boss/AbstractSummon`.

**Add a death trigger**: override `onDeath` (the dying combatant's own effects; see `AspectShift`, `SoulTribute`, `WoundedRetreat`, `Undying`) or `onAnyDeath` (everyone else's effects).

**Add a phase-cycling boss**: extend `Effects/Boss/AbstractPhaseCycle` with `phases()` (self effects plus an ally aura per phase) and `turnsPerPhase()`.

**Add an enemy or encounter**: create the classes in `Content/Enemies` and `Content/Encounters` and add them to `ENEMIES` and `ENCOUNTERS`. Practice battles list every registered encounter automatically.

**Add an engine-level hook**: add a case to `Effects/Hook` (value = method name), add the method to `Contracts/Effect` and `AbstractEffect`, and dispatch it from the engine through `EffectDispatcher`.

## Testing

- Pure engine tests live in `tests/Unit/HeroCombat` (no DB). `Support/BattleBuilder` builds in-memory battles (`->hero()`, `->npc()`, `->withRoster()` for an encounter). `Support/ScriptedRandomSource` makes randomness controllable: evasion never procs and chances succeed unless queued. `BuildsBattles` provides `queue()`, `declare()`, `perform()` and `resolveTurn()`.
- DB flow tests: `tests/Feature/HeroCombat/HeroBattleFlowTest.php`. Pages: `tests/Feature/Http/Dominion/HeroBattlePagesTest.php`. Varos: `tests/Unit/Services/Dominion/HeroBattleServiceTest.php`.
- `php artisan test --compact tests/Unit/HeroCombat`

## Replays

Every battle stores a seed and an initial snapshot, and each action row records its intent's
source (`events[0]`: `selected`, with `source` = queue|ai|forced). `BattleReplayer` restores the
snapshot, replays queued intents (AI and forced intents are deterministic per seed and turn),
gives summons their original ids, and diffs the log turn by turn:

```
php artisan hero-combat:replay {battle}
```

## Old → new mapping

| Old (HeroBattleService / HeroCalculator) | New |
|---|---|
| `has_focus`, channeling `focus +=` | `focused` effect (stacks with Channeling), consumed by Attack-tagged abilities |
| `shield` column, Fortify top-up | `shield` effect with `data.pool` (Replace) |
| `current_action == defend/counter/recover` | `defending`/`countering`/`recovering` stances applied at declare; bosses check `chose()` |
| `status.frostbite` | `frostbitten` stacking debuff |
| `frozen_pending` → `frozen` | `freezing` → (`onExpire`) → `frozen` |
| `increment('attack', 1)` etc. | `forged` / `analyzed` / `outmaneuvered` stacking stat shifts |
| `telegraphed_move` / `telegraphed_order` | `AbstractTelegraph` (`snow_witch_curse`, `admirals_orders`) |
| Tome `base_abilities` merging | `AbstractPhaseCycle` with team auras (`tome_of_power`) |
| `power_source` by display name | `power_source` by template key → `severed` debuff |
| `hero_id === null` means enemy | `team` |
| `winner_combatant_id` | `winning_team` (+ representative id) |
| `limited` actions | `cooldown() = 1` |
| sortBy('fortify') | `priority()` (Fortify = 10) |

Deliberate rebalances: destroying the Tome also removes its active chapter aura from the Lich
King. Counter-attacks no longer inherit the triggering ability's bonus damage. Mending adds base
focus (not effective focus) to Recover.
