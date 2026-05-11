## Magic System v2 — Prototype Changelog

*Implementation plan for shipping the magic-system-design-v2.md system. Runs on a dedicated branch and server — no feature flags needed. Where the design doc has open questions, this changelog picks a concrete default so the prototype is buildable. Defaults are marked **[default]** — they should be playtested and revisited, not treated as final.*

---

## Phase 1 — Adjusted Wizard Power

**Goal:** soft-cap wizard ratio in success rolls and any scaled damage formulas.

### Changes
- `src/Calculators/Dominion/WizardCalculator.php`:
  - Add `getAdjustedWizardRatio(Dominion $d, string $type = 'offense'): float` — returns `2 * raw / (raw + 1)`.
  - Type parameter routes through existing offense/defense modifier split.
- `src/Calculators/Dominion/SpellCalculator.php`: success-rate formula reads `getAdjustedWizardRatio` for both caster and target.
- **Decision [default]:** modifiers (race, tech, wonder, hero, spires) apply to **raw WPA, pre-cap**. The cap is a post-modifier ceiling. Reverse if playtest shows perks feel meaningless.
- **Decision [default]:** keep the two-curve split (info vs. hostile/war) for now. Collapsing into one curve is a follow-up once the cap's effect on the gradient is observed.
- Cyclone & Raid damage: switch `WPA × land` → `AdjustedWPA × land` in the relevant damage builder (in `SpellActionService` and any RaidService).

### Tests
- Unit: `getAdjustedWizardRatio` at WPA 0/0.5/1.0/2.0/5.0 → 0.0/0.667/1.0/1.333/1.667.
- Feature: caster at WPA 2.0 vs. target at WPA 1.0 has success rate close to (not far above) caster at WPA 1.0 vs. target at WPA 1.0.

---

## Phase 2 — Mana Shield

**Goal:** active shield that absorbs spell damage. Once depleted, incoming damage mints mana for the defender, scaling with war duration.

### Migration
- `add_mana_shield_to_dominions`: `mana_shield` int default 0.

### SpellCalculator Additions
`src/Calculators/Dominion/SpellCalculator.php`:
- `getManaShieldCap(Dominion $d): int` — Y, the self-cast ceiling. **[default]** `AdjustedWPA × totalLand × 2.0`.
- `getManaShieldPassiveCap(Dominion $d): int` — X, the passive trickle ceiling. **[default]** `0.5 × getManaShieldCap()`.
- `getManaShieldRegenPerTick(Dominion $d): int` — **[default]** `0.05 × getManaShieldPassiveCap()` per tick (≈20 ticks to fill from empty).
- `getManaShieldRefillAmount(Dominion $d): int` — Mana Shield self spell refills to Y in one cast.
- `getManaShieldFriendlyAmount(Dominion $caster, Dominion $target): int` — **[default]** `0.25 × target->getManaShieldPassiveCap()`, scaled by caster Adjusted WPA.
- `getManaShieldDecayPerTick(Dominion $d): int` — **[default]** none above passive cap. Banking past X simply doesn't happen except briefly after a self-cast (which sits at Y and drains toward X). Add real decay only if banking turns out abusable.
- `getAbsorbedManaPerHit(int $damageDealt, Dominion $target): int` — looks up war start from the war table. **[default]** `damageDealt × min(0.5, 0.05 × warHours)` (caps at 50% absorbed after 10h of war).

### Tick Hook
- `src/Services/Dominion/TickService.php`: each tick, if shield < passive cap, add regen amount. If shield > passive cap (post self-cast or friendly), drain by 5% of (current − passive cap) per tick.

### Damage Pipeline
At every war-spell damage site (currently in `SpellActionService` for Fireball, Lightning Bolt, etc.):
1. Compute raw damage as today.
2. `absorbed = min(damage, $dominion->mana_shield)`; `realDamage = damage − absorbed`.
3. `$dominion->mana_shield -= absorbed`.
4. If `realDamage > 0`, mint `getAbsorbedManaPerHit(realDamage, $dominion)` mana onto defender's `resource_mana`.
5. Apply `realDamage` to peasants/castle as today.

### Removed Passive Defenses
- Vulnerability multiplier on Fireball.
- Masonry Lightning Bolt reduction.
- Wizard Guild peasant protection.
- Rejuvenation on Burning/Storm expiry.

### Immediate Spell Support
Currently all self spells have a duration and persist a row in `dominion_spells`. `mana_shield` is an instant effect with no duration — this code path doesn't exist yet.

- Add `duration` to spell game data (already present for timed spells; set to `0` for immediate spells).
- `SpellActionService::castSelf`: after applying wizard strength cost and success roll, branch on `$spell->duration > 0`:
  - **Timed (> 0):** existing path — upsert row in `dominion_spells` with `expires_at`.
  - **Immediate (= 0):** apply effect directly, no row written to `dominion_spells`.
- `mana_shield` uses the immediate path: calls `getManaShieldRefillAmount` and writes directly to `$dominion->mana_shield`.

### New Spells (game data)
- `mana_shield` self spell: immediate, refills to Y. **[default]** mana cost 5× standard self.
- `lesser_mana_shield` friendly spell: duration-based, adds friendly amount. **[default]** mana cost 3× standard self.

### Tests
- Fireball at full-shield target → 0 peasant loss; shield reduced by raw damage.
- Fireball at empty-shield target after 24h war → defender mana credited per absorbed-mana formula.
- Self Mana Shield cast brings shield to Y; no row written to `dominion_spells`; subsequent ticks decay it toward X.
- Casting a timed self spell still writes a row to `dominion_spells` as before (regression guard).

---

## Phase 3 — Per-Realm Hostile Spell Stacking

**Goal:** debuffs stack across realms (cap 4) with each realm tracked independently. Cleanse can never reduce duration below 1 hour.

### Table Split

Hostile spells get their own table — the existing `dominion_spells` unique constraint on `(dominion_id, spell_id)` is kept and continues to own single-instance effects. Clean ownership boundary:

| Table | Owns | Uniqueness |
|---|---|---|
| `dominion_spells` (existing) | Self buffs, friendly buffs (Arcane Ward, Illumination, Meditation, Energy Mirror), self/friendly cooldowns | One row per (dominion, spell) |
| `dominion_hostile_spells` (new) | Persistent per-realm hostile debuffs (Plague, Insect Swarm, Great Flood, Earthquake, Silence, Burning) | One row per (target, casting realm, spell) |

Counter-spells line up cleanly with the split: **Cleanse** reads `dominion_hostile_spells`; **Dispel** reads `dominion_spells`.

### Migration
- `create_dominion_hostile_spells_table`:
  - `dominion_id` (target), `realm_id` (caster's realm), `spell_key`, `duration` int (ticks remaining), timestamps.
  - Unique index on `(dominion_id, realm_id, spell_key)`.

### Stack Cap

The cap is **calculator-enforced**, not a DB constraint. The DB allows any number of rows; `SpellCalculator::getStackCount()` clamps at `STACK_CAP = 4` when computing effect magnitude. This keeps the schema flexible — tuning the cap (or letting specific spells bypass it) is a code change, not a migration.

### Info Spell Impact

Revelation (and any UI listing active spells on a target) now queries **both** tables and unions the results. Hostile-spell rows display per-realm so the target can see which realms are pressuring them and at what stack level.

### Service Changes
- `SpellActionService::castHostile`: write/refresh row in `dominion_hostile_spells` keyed by `(target, casterRealm, spell)`. Recasting resets `duration` to the spell's base duration.
- `SpellCalculator::isSpellActive($d, $key)`: returns true if any row with `duration > 0` exists.
- New: `SpellCalculator::getStackCount($d, $key)`: `min(rows where duration > 0, STACK_CAP)` with `STACK_CAP = 4`.
- `TickService`: each tick, decrement `duration` by 1 on all `dominion_hostile_spells` rows; delete rows where `duration` reaches 0.
- Effect lookups (e.g. food production penalty for Insect Swarm): multiply per-realm value × stack count.

### Range Enforcement
- `SpellActionService::canCastHostile`: enforce 40–250% range bracket. Closes the "top OP out-ranges debuffs" gap so catch-up stacking can actually engage dominant players.

### Cleanse Mechanics
- Friendly spell `cleanse`:
  - Picks a `spell_key` uniformly at random from active rows on target.
  - For all rows of that key: `duration = max(1, duration − 2)`.
  - **[default]** caster cooldown 4h (per-caster, not per-target).
- Tick-based duration eliminates the end-of-hour race naturally — minimum is 1 tick remaining.

### Tests
- 4 realms cast Insect Swarm → effective penalty = 4× base. 5th realm joins → still 4× (cap).
- Cleanse on target with Plague + Insect Swarm hits one randomly; rows of that type drop by 2 ticks or floor to 1 tick.
- Cleanse on a debuff with 1 tick remaining → still 1 tick remaining (floor enforced).
- Out-of-range hostile cast is rejected.

---

## Phase 4 — Defensive Spells

**Goal:** ship the active defensive bench. Self handles solo defense; friendly versions enable teamplay.

### Damage Log
New table `dominion_damage_log` powers Revive/Repair targeting:
- `dominion_id`, `damage_type` (`peasants` / `walls` / `forges` / `science` / `keep`), `amount`, `at`.
- Written from the damage pipeline (Phase 2 step 5).
- Trimmed to last 24h on tick.

### Self Spells (game data)
| Key | Effect | Cost mult | Cooldown |
|---|---|---|---|
| `revive` | Restore 2% max peasants | 2× | None |
| `resurrection` | Restore up to 60% of `peasants` damage in last 24h | 8× | 12h |
| `repair` | Restore 25% of `walls/forges/science/keep` damage in last 24h | 5× | 6h |
| `energy_mirror` | 30% reflect chance for 6h | 10× | None |

`resurrection` and `repair` read the damage log and cap recovery at the logged amount × percentage. Recovered amounts decrement the log so the same damage can't be re-recovered.

### Friendly Spells (game data)
| Key | Effect | Notes |
|---|---|---|
| `lesser_mana_shield` | Phase 2 amount | — |
| `lesser_revive` | Restores 1% ally max peasants | — |
| `lesser_repair` | Restores 10% of ally castle damage in last 24h | — |
| `cleanse` | Phase 3 | — |
| `meditation` | +50% wizard strength regen for 6h; **breaks on first cast by ally** | Counters Snare |
| `arcane_ward` | Existing | Unchanged |
| `illumination` | Existing | Unchanged |

### Access Rule
- All friendly spells castable by any realm member. **Remove** the existing role gate (Grand Magister / Court Mage).
- Range enforcement: 40–250% of target's land.

### Meditation Cancellation
- `SpellActionService::cast`: at end of any successful cast, if caster has Meditation buff active, expire it now.

### Deferred (not in prototype)
- `lesser_resurrection` — ship after self Resurrection's pacing is validated.
- `spell_reflect` — removed from game data (Energy Mirror covers reflection, percentage-based behavior resists probe-consumption).

### Tests
- Fireball kills 1000 peasants logged → Resurrection recovers up to 600.
- Resurrection cooldown blocks recast for 12h.
- Meditation expires on next cast by ally.

---

## Phase 5 — Offensive Spells

**Goal:** ship the offensive bench with clean counter-relationships. A handful of speculative spells stay deferred until Burning playtest.

### Reworked / New Spells
| Key | Type | Effect | Notes |
|---|---|---|---|
| `fireball` | War | Existing % peasant kill | Now passes through Mana Shield (Phase 2) |
| `burning` | War | DoT: kills 0.5% current peasants per tick for 6 ticks | **Realm-role gated [default]** (e.g. War Chancellor) |
| `lightning_bolt` | War | Existing % castle damage | Through shield |
| `targeted_lightning_walls` / `_forges` / `_science` / `_keep` | War | Hits one improvement at higher % than generic LB | — |
| `mana_burn` | War | Drains 25% of target's current mana **and** 100 shield | Counter to shield + stockpiling |
| `dispel` | Hostile | Reduces a random self-spell duration on target by 2h | — |
| `silence` | Hostile | +50% mana cost on target's friendly spells, 4h | — |
| `doom` | Hostile | Immediate: extends caster's existing debuffs on target by 25% | No row written; effect is baked into existing `duration` values |

### Implementation Notes
- **Burning DoT**: lives in `dominion_hostile_spells` with `spell_key = 'burning'`. No extra columns needed — damage is `0.5% × current peasants`, recomputed each tick. Per-tick processor in `TickService` finds rows with `duration > 0`, applies damage, then decrements `duration` along with all other hostile spell rows.
- **Mana Burn**: trivial subtract from `resource_mana` and `mana_shield`.
- **Dispel**: immediate — picks a random row from `dominion_spells` on target and subtracts 2h from `expires_at`. No row written.
- **Doom**: immediate — iterates caster's existing rows in `dominion_hostile_spells` for the target and extends each `duration` by 25% (rounded up). No row written.
- **Silence**: persistent debuff, lives in `dominion_hostile_spells` like other stacking debuffs.
- Burning **bypasses the per-realm stacking cap** in `SpellCalculator::getStackCount` because access is already restricted to roles. Fold into stacking logic only if access is later widened.

### Deferred
- `lightning_storm` (castle DoT) — wait on Burning playtest.
- `ruination` (1h repair lockout) — wait on Repair playtest.
- `miasma` (anti-ward) — wait on Ward usage data.
- "prevent revives 1h" — wait on Revive playtest.

### Tests
- Burning ticks 6 times then expires.
- Mana Burn on full-shield target drains shield and mana.
- Doom on a target with active Plague extends Plague's `duration` by 25% (rounded up).

---

## Phase 6 — Specialization via Mastery

**Goal:** unlock a specialization path at a Mastery threshold. One choice, locked for the round.

### Migration
- `add_specialization_path_to_dominions`: `specialization_path` string nullable. Enum values: `debuffer`, `opener`, `finisher`, `protector`, `cleanser`, `healer`.

### Threshold & Selection
- **[default]** Threshold = 500 Wizard Mastery.
- New method `WizardCalculator::canChoosePath(Dominion $d): bool`.
- Controller `MagicSpecializationController@store` writes the path. Locked once set.

### Perk Wiring
New `MagicSpecializationCalculator`, one method per axis returning a multiplier:
- `getDebuffDurationBonus($d)` — +25% hostile spell duration if `debuffer`.
- `getDamageVsShieldBonus($d)` — +25% effective shield bypass if `opener`.
- `getDamageNoShieldBonus($d)` — +25% damage when target shield = 0 if `finisher`.
- `getFriendlyEffectBonus($d)` — +25% to friendly spell magnitudes if `protector`.
- `getCleanseBonus($d)` — Cleanse reduces 4h instead of 2h if `cleanser`.
- `getReviveBonus($d)` — Revive/Resurrection +25% if `healer`.

Each is read at the relevant calculator/service site.

### UI
- Magic page section: specialization picker visible only when threshold met and not yet chosen.

### Tests
- Mastery 499 → no choice; mastery 500 → choice available.
- `cleanser` makes Cleanse remove 4h instead of 2h.

---

## Phase 7 — Shadow League Cleanup

### Removals (v2 flag on)
- Drop `chaos` score from spell-success and spell-failure paths.
- Remove critical-failure reflection logic from `SpellActionService`.
- Remove Chaos-only access overrides for hostile/war spells (range and 3-day-into-round timing now apply uniformly).

### Audit (flag for review, do not auto-port)
- SL info ops access — likely retained.
- SL lower losses on failed casts — likely retained as "early-round risk-taker" perk.
- SL exclusive spell access — re-evaluate per spell. Default: nothing carries over.

### Friendly Spell Isolation (open question)
- New rule: friendly spells from non-SL casters cannot target SL members.
- `SpellActionService::castFriendly`: reject when caster.is_not_sl AND target.is_sl.
- Listed as an open question in the design doc — implement behind a simple config constant so it can be toggled during playtest.

---

## Phase 8 — Misc Cleanup

### Wizard Guilds
- Restore wizard strength recovery perk: each guild adds **[default]** 0.05 to per-tick recovery.
- Wire into `WizardCalculator::getStrengthRecovery`.
- Dark Elf Spellwright's Calling perks unchanged.

### Spy Strength Counter
- New hostile spell `disperse_spies` (or rework Disband Spies):
  - Drains 10 from target spy strength.
  - **[default]** mana cost 4× standard, requires Adjusted WPA ≥ 0.5.

### Mutual War Perk
- Replace "reduced wizard losses on failure during mutual war" with **+10% spell damage during mutual war** for all war spells.
- Modify damage path in `SpellActionService`.

### Cyclone & Raid
- Already covered by Phase 1 (Adjusted WPA × land).

---

## Phase 9 — UI

- Status page: Mana Shield bar (current / cap) above wizard strength bar. Color shifts red when below 100.
- Active debuff panel: list each debuff with **stack count** and contributing realm count ("Insect Swarm × 3 realms").
- Magic page: regroup spells into Self / Defensive / Hostile / War / Friendly tabs. Per-spell cooldown timers visible.
- Damage log page: simple list of recent peasant/castle damage events for Revive/Repair targeting context.
- Specialization picker (Phase 6) appears in magic section once threshold met.

---

## Phase 10 — Test Environment Plumbing

- Dev artisan: `php artisan dev:magic-v2:simulate-war` — spins up two seeded realms, simulates 24h of war ticks for shield/absorption tuning. Outputs CSV of mana spent vs. mana absorbed vs. damage dealt.
- PHPUnit feature suite: `tests/Feature/MagicV2/` — one file per phase covering canonical interactions.
- Add a `/dev/magic-v2/dashboard` route (gated on local env) showing current shield / debuff stack / damage log for a chosen dominion. Useful for tuning sessions.

---

## Suggested Ship Order

1. **Phase 1** — Adjusted WPA. Small, isolated, validates the formula.
2. **Phase 2** — Mana Shield. Biggest single piece; everything defensive depends on it.
3. **Phase 3** — Per-realm stacking. Independent of shield; can be parallelized with Phase 2.
4. **Phase 4** — Defensive spells. Needs shield + damage log.
5. **Phase 5** — Offensive spells. Needs shield + damage log.
6. **Phase 6** — Specialization.
7. **Phase 7** — Shadow League cleanup.
8. **Phase 8** — Misc cleanup.
9. **Phase 9** — UI. Land incrementally as backend phases complete.
10. **Phase 10** — Test plumbing. Build alongside; finalize after Phase 5.

---

## Tuning Constants Summary

All marked **[default]** above; collected here for ease of editing during playtest.

| Constant | Default | Lives in |
|---|---|---|
| Stack cap (hostile per-realm) | 4 | `SpellCalculator::STACK_CAP` |
| Cleanse minimum remaining | 1 tick | `SpellActionService::cleanse` |
| Cleanse cooldown | 4h | game data |
| Mana Shield Y (cap) | `AdjWPA × land × 2.0` | `SpellCalculator::getManaShieldCap` |
| Mana Shield X (passive cap) | `0.5 × Y` | `SpellCalculator::getManaShieldPassiveCap` |
| Shield regen per tick | `0.05 × X` | `SpellCalculator::getManaShieldRegenPerTick` |
| Friendly shield amount | `0.25 × target X` | `SpellCalculator::getManaShieldFriendlyAmount` |
| Mana absorption fraction | `min(0.5, 0.05 × warHours)` | `SpellCalculator::getAbsorbedManaPerHit` |
| Mastery specialization threshold | 500 | `WizardCalculator::canChoosePath` |
| Specialization perk magnitude | +25% | `MagicSpecializationCalculator` |
| Mutual war damage bonus | +10% | `SpellActionService` |
| Wizard Guild strength regen | +0.05 / tick / guild | `WizardCalculator::getStrengthRecovery` |
| Burning DoT | 0.5% peasants × 6 ticks | game data |
| Mana Burn drain | 25% mana + 100 shield | game data |
