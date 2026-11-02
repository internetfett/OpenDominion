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
    TurnLedger.php      a turn's pending damage and healing, settled together
    BattleEngine.php    advance() loops turns while everyone is ready
    IntentDecider.php   forced → queued → AI
    ActionValidator.php cooldowns, charges, tag gating, canUse; queue projection
    ActionContext.php   what abilities receive: actor, targets, helpers
    Damage/             DamageRequest → DamageResolver → DamageResult (one pipeline for every hit)
    Effects/            EffectInstance, EffectManager (apply/stack/tick/remove), EffectDispatcher (ordered hooks)
    Stats/              Stat enum, Modifier, StatCalculator
    Targeting/          TargetRule enum, TargetResolver
    Events/             BattleLog, LogEntry, BattleEvent (structured), EventType, MessageKind
    Random/             RandomSource, SeededRandomSource
    Victory/            VictoryCondition, LastTeamStanding
  Content/          Everything game-specific. Add new content here.
    Abilities/          Attack, Defend, Focus, Counter, Recover, class abilities, Boss/ signature moves
    Effects/            Stances/, Passives/, Boss/ (telegraphs, phase cycles), buffs/debuffs
    Ai/WeightedStrategy presets (balanced, aggressive, defensive, pirate, …)
    Enemies/            EnemyTemplate classes
    Encounters/         Encounter classes (rosters, grants, scaling, victory rules)
    Loadouts/HeroClassLoadouts  basic abilities, class → signature, grants that replace a basic
  Registry/CombatRegistry  explicit key → definition maps
  Persistence/      BattleRepository (models ⇄ state, row lock), CombatantFactory, EloquentCombatantSpawner,
                    StateSnapshot, BattleReplayer
  Presentation/     BattlePresenter / BattleView (view model for Blade), TurnSummary (phased turn results)
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
           4. Order       priority desc, then healing after damage, then combatant id
           5. Resolve     per intent: resolve targets → Ability::resolve → start cooldown, spend charge
                          → AbilityUsed hooks → OwnerActionEnd expiry. Damage and healing go into
                          the turn ledger; health does not change yet
           6. Settle      apply each combatant's damage and healing together → lethal saves
                          → process deaths → run afterActions callbacks
           7. TurnEnd hooks → tick TurnEnd durations → Encounter::onTurnEnd → process deaths
           8. VictoryCondition → finished / winning team, else turn++
     └─ persist state + log rows → if finished: HeroBattleOutcomeService::finalize
```

- **Simultaneous semantics.** Turns are fully simultaneous: the order of combatant ids never decides who lives. Nobody's health changes and nobody falls until every action has resolved, so healing can save someone from a killing blow, a falling hero still acts and Counters, and an attack on a falling target stays on them (overkill is wasted). Anyone summoned mid-turn acts from the next turn.
- **Stats hold still during a turn.** Anything that would change a stat because of an action goes through `Battle::afterActions()` and lands at the settle step: gaining and spending Focus, Battle Fury streaks, Forge, Combat Analysis, Tactical Awareness, Sunder, Riposte, Spellbrand and Shadow Dance. Stances apply at declare, so they cover the whole turn. Boss spells (Magic Ward, Backlash, Silence) still apply when they resolve, which can matter in PvE only.
- **Outside a turn's actions** (turn start, turn end, direct `perform()` calls in tests) there is no ledger: damage and healing apply at once and `afterActions()` runs its callback immediately.
- **Readiness.** NPCs are always ready. A human is ready when they have a queued action or are automated. `checkTime()` drains time banks and switches anyone at zero to automated.
- **Orders.** `HeroActionService::queueAction()` queues one action for one combatant. `queueOrders()` (`POST heroes/battles/orders`, `orders[combatantId][action|target]`) locks in one action for every living hero the dominion pilots in a transaction, all or none; every hero not on autopilot needs an order. The controller then calls `processTurn()` once.
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
| `hero_battle_turns` | one row per resolved turn: `events`, the turn's structured events in order. Turn 0 holds setup events (already in the initial snapshot) |

An effect instance is serialized as `{key, src, turns, stacks, data, turn, seq}`. `seq` is the
global apply order, which the dispatcher uses to break ties.

## Effects

Buffs, debuffs, statuses **and passives** are all `Effect` classes. Passives are effects of kind
`Innate`: permanent and not dispellable. Definitions are stateless singletons; per-application
state lives on the `EffectInstance` (stacks, remaining turns, `data`).

- **Scopes.** An effect lives on a combatant (`EffectManager::apply`), a team (`applyToTeam`) or the battlefield (`applyToField`). Team and field instances apply to whoever `Effect::appliesTo()` accepts. The default excludes ids listed in `data.exclude`.
- **Stacking.** `Refresh` resets the duration and merges data. `Stack` adds stacks up to `maxStacks()`, which may depend on the owner (Focused stacks without limit under Channeling). `Replace` swaps in a new instance. `Independent` keeps every application.
- **Expiry timing.** `TurnEnd` (default), `OwnerActionEnd`, `OnDamageTaken`, or `OnConsume` (removed only explicitly). An effect applied *during* end-of-turn ticking is not ticked in that same pass, which is how Freezing hands over to Frozen for exactly the next turn.
- **Tags** (`CombatTag`). Effects grant tags. Abilities declare `blockedByTags()` (default: Stunned) and `requiredTags()`. Focus and Recover are also blocked by `Silenced`. Effects can declare `immuneToTags()`. Cleanse removes `Curse`-tagged effects. Stances are queried by tag (`Defending`, `Countering`).
- **Modifiers.** `effective = round((base + Σflat) × (1 + Σpercent))`, then overrides (e.g. Frozen → 0), clamped at 0. A modifier must not compute other *effective* stats inside `modifiers()`, because that recurses. Use `baseStat()` (see Mending).

### Hook reference (all no-op in `AbstractEffect`)

| Hook | Runs for | Typical use |
|---|---|---|
| `onApply` / `onExpire` / `onRemove` | the instance | setup; hand-offs (Freezing→Frozen); `onRemove` covers dispels too |
| `onTurnStart` / `onTurnEnd` | every instance in the battle | telegraph rolls, phase changes, regen/DoT, start-of-turn snapshots (Backstab) |
| `forcedIntent` | the acting combatant's effects | telegraphed moves, charged attacks |
| `beforeDamageDealt` | attacker's effects | add bonus damage / defend modifier (Crushing Blow, Backstab) |
| `redirectDamage` | target's effects | Cover |
| `onEvaded` | target's effects | Elusive sets `evadeMultiplier = 0`; Shadow Dance grants Focus |
| `beforeDamageTaken` | target's effects | Shield absorbs (priority 10) |
| `onLethalDamage` → bool | target's effects | Hardiness (priority -10, after shields) |
| `afterDamageTaken` / `afterDamageDealt` | target / attacker | Lifesteal, Frostbite stacks |
| `onAbilityDeclared` | actor's own effects, right after `Ability::onDeclare` | swap or adjust stances (no current content uses it) |
| `onAbilityUsed` | actor's effects | consume Focused after an Attack-tagged ability; Sunder, Second Wind; Battle Fury streaks |
| `adjustCooldown` → int | holder's effects, when a cooldown starts | change an ability's cooldown (no current content uses it) |
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
5. Add to the turn ledger (or, outside a turn's actions, lethal save and apply at once)
6. `OnDamageTaken` expiry → `afterDamageTaken` / `afterDamageDealt`
7. Counter-attack if the hit is counterable and whoever it struck or was aimed at holds `Countering` (so a Counter fires through a guard): attack + counter vs defense, no evasion, not shielded, not itself counterable, not redirected by a guard, × hits

Lethal saves (`onLethalDamage`, unless a hit set `bypassLethalSave`) run at the settle step against the whole turn's damage and healing, so Hardiness is only spent if the hero would really fall. `DamageResult::$counterer` names who struck back. Use `ActionContext::sayIfFalls()` for a line that should only appear if the target actually falls, once healing is counted.

`ActionContext::attack()` builds a standard request. `ActionContext::flatDamage()` builds fixed
damage that ignores defense, evasion and counters (boss moves).

## Abilities, AI, targeting

- **Abilities** implement `key/name/description/resolve`. `AbstractAbility` supplies defaults: SingleEnemy, priority 0, no cooldown, unlimited charges. `cooldown()` returns the turns to wait after use; 1 means "not twice in a row" (Focus, Counter, Recover, class abilities). Both `cooldown()` and `maxCharges()` receive the actor, so they can vary.
- **Queue validation** (`ActionValidator::canQueue`) projects cooldowns and charges forward through the actions already queued. At resolution, an invalid queued action falls back to AI.
- **Target rules**: Self, SingleEnemy, SingleAlly, SingleAny, AllEnemies, AllAllies, AllOthers, RandomEnemy. A dead single-enemy target is replaced with a random living enemy; dead ally targets fizzle. `Provoked` forces single-enemy targeting onto the provoker. `SingleAlly` with no target chosen means yourself (`TargetRule::defaultsToSelf()`).
- **Defend, Focus and Recover take an ally target.** In 1v1 the only ally is yourself.
  - **Defend** on yourself is `defending` (defense ×2). Defending again right after adds `exposed`, so the turn's defense is (20 − 5) × 2 = 30. Defend and Guard share the `defend` key, so `Ability::recordedAction()` stores a Guard as `guard` in `lastAction` to keep it out of the streak, and `Ability::warning()` labels the button.
  - **Guard** is Defend on an ally: `guarding` (a `Defending`-tagged stance, defense +50%, so 30 at base) plus `covered` on the ally, and hits aimed at the ally strike the guard. It has no streak penalty and doesn't count toward Defend's, so Defend and Guard can alternate. Two heroes guarding each other swap the hits aimed at them and both keep the bonus; redirects happen once per hit, so they can't loop. A Counter's retaliation is never redirected.
  - **Rally** is Focus on an ally: `rallied` at declare adds the caster's focus stat to that ally's attacks this turn only (not Counters), and stacks with their own Focus. It shares Focus's cooldown and can be used while Focused; `Ability::canTarget()` is what stops a Focused hero focusing themselves. The AI never rallies by choice.
  - **Recover** heals the target with the caster's recover stat, with the `recovering` −5 defense on the caster. Each combatant has 3 charges per battle in every kind of battle, shared between self and ally, enemies included.
- **Cooldowns can be adjusted by effects** (`Effect::adjustCooldown`, applied by `ActionValidator::cooldownFor`). No technique uses this now; techniques that removed cooldowns were cut for changing the basic actions too much.
- **AI**: `WeightedStrategy` picks a usable ability by weight. It drops Fortify while shielded, drops Recover when it would overheal, and doubles Recover at ≤40 HP. For ally-targeted abilities, healing goes to the most wounded ally; Defend stays on yourself unless a partner is at ≤40 HP. Player-selectable strategies are flagged `playerSelectable`.
- **Pass** is chosen when nothing is usable (e.g. while stunned).

## Combat loadouts

Techniques, class abilities and gear are not hero upgrades. They're `LoadoutOption`s: small data records in `Content/Loadouts/HeroLoadoutOptions.php`, registered in `CombatRegistry` by `HeroCombatServiceProvider::buildRegistry()`. No database is involved, so the simulator and `BuildFactory` use the same data as the game. Hero upgrades (`heroes.yml`) only cover dominion bonuses: doctrine at level 1, dominion upgrades on levels 2, 4 and 6, and Scion and Scholar directives. None of them grant combat stats.

A hero gains access automatically and picks a loadout on the heroes page at any time:

| Slot (`LoadoutSlot`) | Capacity | Access | Notes |
|---|---|---|---|
| Technique | 1 | Level 3 (Technique I), level 7 (Technique II) | Both tiers share the one slot |
| Ability | 1 | Class level 5 in that class, active or not | No hero level requirement |
| Gear | 2 | Level 9 | Two different pieces; only gear changes stats |

Rules live in `HeroCalculator`:

- `getAvailableLoadoutOptions()` is the only place that decides access; new gear sources belong here. Hero-level requirements use `getCombatLevel()`, the highest level in any class, so changing class never removes access. Class signatures use `getClassLevel()`.
- `getLoadout()` resolves the stored keys in `heroes.loadout` (JSON: `{"technique": "backstab", "ability": null, "gear": ["greatsword"]}`) to options by slot, dropping anything the hero can't use or that exceeds capacity. Spawning, stats, the page and the badge all read through it.
- `getEmptyLoadoutSlotCount()` feeds the sidebar badge (added to the unlockable upgrade count in `ComposerServiceProvider`).
- `getNewLoadoutSlots()` compares available options before and after an experience change, and fills `unlocks` on the `hero_level` notification. It compares options rather than levels because a high-level hero can reach class level 5 in a new class.

`HeroActionService::updateLoadout()` replaces the whole loadout (POST `heroes.loadout`), rejecting unavailable keys, options in the wrong slot, too much gear and duplicate gear.

`CombatantFactory::addHero()` reads `getLoadout()`: ability keys join the action bar through `HeroClassLoadouts::withGrants()` (Fortify replaces Defend), effect keys are applied as innate effects, and gear reaches the stats through `HeroCalculator::getHeroCombatStats()`. A borrowed hero uses its owner's loadout because the owner's `Hero` is passed in. Battles in progress are unaffected by loadout changes, since abilities and effects are stored on the combatant; `BattleReplayer` never reads the hero.

A hero's loadout isn't shown in info ops (`InfoMapper` only maps upgrades).

**Technique I** (level 3, `Content/Effects/Techniques/`):

| Technique | Rule (constants on the class) |
|---|---|
| `battle_fury` | each consecutive Attack: +4 attack, −2 defense (max 3 stacks); any other action resets it |
| `restoration` | Recover heals 80% now and 10% at the start of each of the next 3 turns (`restoring`); keeps the −5 defense penalty; recasting forfeits the remaining ticks |
| `mana_shield` | +3 defense while Focused (kept through Defend, Recover and Counter; ends when an attack spends the Focus) |
| `shadow_dance` | evading an attack grants Focus (only if not already Focused) |
| `backstab` | attacks deal +5 damage against an opponent that was Focused at the start of the turn (snapshot in `onTurnStart`) |
| `feint` | +10 evasion on a turn you Attack (reads `Battle::intentOf()`; no status) |

Focus never stacks for heroes now (`Focused::maxStacks()` is 1 unless Channeling).

**Technique II** (level 7) apply a shared one-turn status. The statuses check the holder, not who applied them, so in 1v1 you set up and cash in yourself and in 2v2 an ally can too. Each lands at the settle step with duration 2, so it covers exactly the following turn.

| Technique | Trigger → status |
|---|---|
| `sunder` | after your attack resolves against a Defending enemy → `exposed` on it (−5 defense) |
| `shield_bash` | an enemy attacks you while you're Defending (even a fully blocked hit; works when guarding) → `staggered` on the attacker (Counter blocked via `CombatTag::Staggered`) |
| `riposte` | your Counter deals damage → `marked` on the attacker |
| `spellbrand` | your Focused attack hits without being evaded → `marked` on the target |
| `second_wind` | Recover → `invigorated` (+5 attack) on you, and on the ally you healed |

`marked`: attacks (not counters) that get through deal +5 damage; runs before shields (`handlerPriority` 20) and does nothing to a fully blocked hit.

**Class Abilities**: `HeroClassLoadouts::SIGNATURES` maps each class to its signature. A hero can equip a signature once `getClassLevel()` reaches `HeroClassLoadouts::UNLOCK_CLASS_LEVEL` (5) in that class, active or not (inactive levels come from `class_data` experience). Each signature uses its class's icon; the loadout form shows the requirement (`HeroHelper::getLoadoutOptionRequirement()`).

| Class | Signature | Kind |
|---|---|---|
| Alchemist | `volatile_mixture` | action |
| Architect | `fortify` (replaces Defend) | action |
| Blacksmith | `forge` | action |
| Engineer | `tactical_awareness` | action |
| Scholar | `combat_analysis` | action |
| Infiltrator | `shadow_strike` | action |
| Farmer | `hardiness` | passive |
| Healer | `mending` | passive |
| Sorcerer | `channeling` | passive |
| Scion | `last_stand` | passive |

**Gear** is plain data: each option's `stats` are summed by `HeroCalculator::getHeroCombatStats()`.

The heroes page header has a Dominion Bonuses column (upgrade tiles, `partials/dominion/hero-upgrade-tile`, then class bonuses) beside a Hero Battles column (four loadout tiles, `partials/dominion/hero-loadout-tile`, then rating and stats). Tile text stays on one line so chosen and empty tiles match in height, and chosen tiles show the full description in a tooltip. An empty loadout tile scrolls to and highlights the rows the hero can equip. Below the header, the Hero Upgrades form is followed by the Combat Loadout form (`#hero-loadout`). Display text comes from `HeroHelper`: `getLoadoutOptionName()`, `getLoadoutOptionDescription()`, `getLoadoutOptionRequirement()` and `getLoadoutSlotRequirement()`.

## Parties and borrowed heroes

- `Encounter::maxPartySize()` (default 1). Above 1, a pilot may bring realmmates' heroes. Party-enabled encounters are designed for the party size: there's no automatic scaling.
- `hero_combatants.controller_dominion_id` is who pilots a combatant; `dominion_id` stays the owner. `CombatantState::$controllerDominionId` carries it through the engine.
- `HeroBattleService::createEncounterBattle(..., $companions)` / `createPracticeBattle(..., $companions)` validate party size, same realm and round, and no duplicates. `availableCompanions()` lists every other hero in the realm. Any number of realmmates can borrow the same hero at once (each battle has its own combatant row).
- Everything player-facing goes through the controller: queueing and strategy changes (`HeroCombatant::isControlledBy()`), "battle in progress" checks (`hasBattleInProgress()`), the battle list (`HeroBattle::scopeControlledBy()`), raid objective completion (`HeroBattle::hasWinningController()`). Battles where your hero was borrowed don't block you or appear as yours.
- Raid wins credit one contribution per controller dominion. Owners of borrowed heroes get nothing for now.
- UI: the raid hero tactic form and the practice page show a "Bring a realmmate's hero" select only when the encounter's `maxPartySize() > 1`. On the practice page, script follows the selected encounter and clears the choice when it hides the select; its options include `HeroHelper::getLoadoutSummary()` (technique and ability names, no gear). The battle page (`partials/dominion/hero-battle`, also used by the report) has a battle bar (mode, title, turn, time left, who the turn waits on), hero cards per side (compact grid for three or more), the turn summary and, for heroes you pilot, the orders panel (`hero-battle-orders`): one always-visible panel per hero (`BattleView::commandable()`), target pickers from `BattleView::targetOptions()` ("Yourself", "Guard X", "Heal X", enemies), and a Lock in button that posts every hero's order to `heroes/battles/orders`. Heroes on autopilot show a Cancel autopilot button instead. Queued turns (only when `BattleView::canQueueAhead()`) show on the panel with remove links and as a Plan line on the hero card.

## Daily PvP experience

`HeroBattleOutcomeService::grantDailyExperience()`: when a PvP battle (including tournaments) finishes, each hero whose combatant still has time bank left gains, once per round day (`Round::daysInRound()` of `last_pvp_reward_at` differs from today's, so days roll over at the round's start time), a flat `DAILY_PVP_EXPERIENCE` (25, no experience multipliers), win or lose. Tracked in `heroes.last_pvp_reward_at`; a status line is added to the battle log.

## Analysis: Tactician and the simulator (offline only)

`src/HeroCombat/Analysis/` holds tools for studying 1v1 PvP balance. **Nothing here is registered in the game**; `BattleSimulator` builds its own registry copy.

- `TurnPreview` copies a battle through `StateSnapshot` (no queues or intents, so nothing private leaks), resolves one turn with forced actions, and enumerates every evasion outcome via `Battle::$evasionRoll`, weighting each by probability. It returns a `TurnOutcome` (expected health, attack potential, death chance per combatant), cached for the current turn.
- `MatrixGameSolver` solves a zero-sum matrix game exactly (simplex on the column player's LP; the row mix comes from the duals).
- `TacticianStrategy` (`tactician:balanced|aggressive|defensive`, from `TacticianProfile`) previews every pairing of both sides' legal actions (including granted abilities), scores outcomes (damage dealt/taken, healing, change in damage edge, win/loss chance), solves for a mixed strategy and samples it. It looks one turn ahead; the damage edge (`TurnPreview::offense`) carries setup value: next attack's damage against the opponent's defense (Focus, Exposed, Marked), a quarter of the counter threat (none while Staggered), and battle-long stat shifts (`AbstractStatShift`: Forge, Combat Analysis, Tactical Awareness) counted over 4 more turns. `temperature` blends toward uniform play to make it weaker.
- `BuildFactory` parses specs like `level=9;technique=backstab;ability=shadow_strike;gear=greatsword,plate_armor` from the registry, enforcing slot capacity, levels and distinct gear. A build's level stands in for every class level, so any signature is open at level 5. `BattleSimulator::run()` plays seeded battles (alternating combatant ids); `balancedScore()` also plays the swapped matchup on the same seeds to cancel seed luck.
- Command: `php artisan hero-combat:simulate --a=<spec> --b=<spec> [--strategy-a=tactician:balanced] [--strategy-b=balanced] [--temperature=0.2] [--battles=200] [--seed=1]`, or `--grid=techniques|abilities|gear [--level=9]` for a round-robin score matrix. Expect about 0.1s per Tactician battle.

## Balance simulator (`scripts/hero-combat-sim`)

A standalone Rust crate with no dependencies, for rule questions the PHP Tactician is too slow for (2v2 has up to 6,561 action pairings a turn). Nothing in it reaches the game. It models base actions only, without loadouts.

- `src/game.rs` is the rules, with a flag per rule; `Rules::candidate()` is what the engine implements. `src/train.rs` and `src/value.rs` learn a win-probability table per ruleset from self-play, `src/play.rs` and `src/solver.rs` solve each turn as a simultaneous game between both teams' joint actions, `src/exact.rs` solves 1v1 exactly, and `src/eval.rs` measures viability.
- **Viable** means an option gives up at most 3 points of win chance against the opponent's solved mix, on turns where each team has a 10–90% chance to win.
- **Fidelity:** `php scripts/hero-combat-sim/engine_fixtures.php 30000 41 > scripts/hero-combat-sim/fixtures/engine.txt`, then `hero-combat-sim diff-engine scripts/hero-combat-sim/fixtures/engine.txt`, replays real engine turns and compares every evasion outcome, health and legal action. Re-run it after any rule change. The fixtures set remaining charges directly and can't see a charge being spent, so `CombatRulesTest` covers that.
- `cargo build --release`, then `hero-combat-sim run <label> "guard=30;charges=3;rally=1;repeat=30;ctg=1;sim=1" --seed 1 --gens 30 --games 2000 --battles 2000`. Each ruleset trains from scratch in about a minute. Use two seeds and treat a split between them as a warning. `exact "<rules>" --compare` solves 1v1 and checks the learned player against it.

## Encounters and enemies

An `EnemyTemplate` gives stats, active abilities (`AbstractEnemy` adds the five basics), innate
effects with data, and an AI key. An `Encounter` gives a roster (template, display name, stat and
effect overrides) built from an `EncounterContext` (raid `priorWins`, the leader's stats),
optional `playerGrants()` (class-conditional abilities), a `VictoryCondition`, an `onTurnEnd`
script hook, and an optional victory line. Keys must match the `encounter` attribute raids use on
their `RaidObjectiveTactic` rows.

All 18 original encounters are ported, plus `grand_magister`:

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
| `grand_magister` | `grand_magister` AI weights short spells (cooldown 3, 2-turn effects unless noted): `magic_ward` (halves damage taken, 4 turns), `backlash` (attackers take 50% back, 3 turns), `silence` → `silenced` (blocks Focus/Recover, 3 turns), `arcane_conduit` (forced ×2 attack next turn). No telegraph, no grants. Party of 2 (`maxPartySize()`) |

The old unused "story" encounters (wolf, bandit, imp, cultist) were never reachable and were
not ported.

## Recipes

**Add an ability**
1. Create `Content/Abilities/MyAbility.php` extending `AbstractAbility` (or `AbstractAttack` for weapon hits; override `damageOptions()`).
2. Implement `resolve(ActionContext $context)` using `$context->attack()`, `flatDamage()`, `heal()`, `applyEffect()`, `summon()`, `chose($target, 'defend')` and `say('messageKey', [...])`.
3. Add it to `HeroCombatServiceProvider::ABILITIES`, then grant it through a template, a loadout or `Encounter::playerGrants()`.

**Add a buff or debuff**: extend `AbstractEffect`. Set `kind()`, `stacking()`, `defaultDuration()` and `tags()`, and return modifiers or override hooks. Register it in `EFFECTS` and apply it with `$context->applyEffect($target, 'key', $turns, $stacks, $data)`.

**Add a passive**: extend `AbstractPassive` (or `AbstractStatPassive` for flat stat bonuses, optionally gated to low health). Give it to an enemy through `EnemyTemplate::effects()`, or to heroes as a technique or class signature (a `LoadoutOption` in `HeroLoadoutOptions`, plus `HeroClassLoadouts::SIGNATURES` for a class).

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

## Turn log and summaries

Everything that happens is a `BattleEvent`: `selected`, `damage`, `heal`, `effect_applied`,
`effect_removed`, `summoned`, `died`, `revived`, `fizzled`, `message` and `reward`.
`BattleRepository` stores each turn's events, in order, as one `hero_battle_turns` row.

- `damage` carries its breakdown: `attack`, `focus`, `counter_bonus`, `bonus`, `defense`,
  `defending`, `flat`, `multiplier`, `hits`, plus `raw` (after defense), `evaded`, `absorbed`,
  `counter` and `redirected_from` (a guard took the hit). `heal` carries `via`, the ability or
  effect key that healed.
- `Battle::say()` records a `message` event alongside the text line. Its `MessageKind` is
  `narration` (stances, telegraphs, flavour), `detail` (only restates a damage or heal event:
  `ActionContext::DETAIL_MESSAGES` templates and a few effect lines) or `outcome` (victory text).
  Pass `MessageKind::Detail` for any new line that only repeats a number already in an event.
- `TurnSummary` groups a turn into Actions, Status, Damage, Healing, Defeats and Result. Lines are
  segment lists (name with side, damage, heal, math, effect, tag, text); `TurnSummary::html()`
  escapes and renders them. It adds greyed lines for a Defend nobody tested and a Counter never
  triggered, an `auto` tag for automated player choices and a one-line gist for collapsed turns.
  Within a phase the order is the resolution order.
- `BattleView::turns()` builds the summaries from the viewer's side (allies blue, opponents red),
  rendered by `partials/dominion/hero-turn-summary` on the battle and report pages.

## Replays

Every battle stores a seed and an initial snapshot, and each `selected` event records its intent's
source (queue|ai|forced). `BattleReplayer` restores the snapshot, replays queued intents (AI and
forced intents are deterministic per seed and turn), gives summons their original ids, and
compares each turn's events with the stored ones (`reward` events are added after the battle and
are skipped):

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
