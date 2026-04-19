# Magic System Redesign v3

*Informed by [magic-system-issues.md](magic-system-issues.md), previous redesign attempts (v1, v2), and the [v2 critique](magic-system-redesign-v2-critique.md).*

---

## Lessons From Previous Attempts

The v1 and v2 redesigns correctly identified the structural problems and proposed the right core fixes: split offense from defense, eliminate wizard death on failure, add dispel mechanics, remove mana decay. The v2 critique confirmed these decisions are sound.

Where they went wrong was complexity. The v2 redesign replaced 3 core resources with 14 named terms, added a 4×4 attunement matrix, 5 independent blight stack types, a stance sub-system, mastery paths, surge charges, and an arcane congress mechanic. It traded "opaque and unfair" for "overcomplicated and unlearnable."

**This redesign takes a different approach: minimum viable changes to solve maximum issues.** No new resource types. No new progression axes. No sub-systems. Every change either modifies an existing mechanic or replaces a broken one with a simpler alternative. A player familiar with the current system should be able to understand every change in this document in one reading.

---

## What Stays The Same

These elements are preserved unchanged:

- **Wizard ratio formula** — `(Wizards + Archmages×2 + Partial Wizard Units) / Land`
- **Wizard strength** — 0–100+ stamina pool, consumed by casting, recovers per tick
- **Mana production** — Towers produce mana; mana costs scale with land size
- **Mana decay** — 2% per tick (v2 removed this; we keep it because it creates casting tempo pressure that rewards active play over hoarding)
- **Self spells** — all generic and racial self spells unchanged, always succeed
- **Info spells** — Clear Sight, Vision, Revelation, Disclosure unchanged
- **Amplify Magic** — unchanged
- **Spell duration and recasting** — unchanged
- **Non-stacking rule** — same-type bonuses don't stack, highest applies
- **Cyclone** — wonder damage unchanged

---

## What Changes

### 1. Decoupled Offense and Defense

*Solves: Issue #1 (ratio arms race), Issue #8 (realm composition)*

**Current problem:** Wizard ratio determines both spell success (offense) and spell resistance (defense). Explorers dominate both because they have more population for wizards.

**Change:** Split into two independent calculations.

#### Spell Power (Offense)

Determines success rate and damage when casting hostile, war, and info spells against a target.

```
Spell Power = Wizard Ratio × Offense Multiplier
```

The **Offense Multiplier** is a composite of (base 1.0 +):
- Race perks (unchanged)
- Spires castle improvement (unchanged)
- Technology (unchanged)
- Hero perks (unchanged)
- Active spell buffs (unchanged)
- Wonders (unchanged)
- **Prestige bonus: prestige / 5,000** (doubled from current prestige / 10,000)
- **War bonus: +5% during one-sided escalated war, +10% during mutual escalated war** (new)
- **Invasion momentum: +8% for 12 hours after a successful invasion** (new)

The prestige, war, and invasion bonuses give attackers competitive spell power that can close the gap against an explorer's raw ratio advantage. An attacker with 400 prestige who just hit someone gets +8% from prestige and +8% from invasion momentum — a +16% power spike that compensates for having fewer wizards.

#### Spell Resistance (Defense)

Determines the difficulty of landing spells on this dominion and reduces incoming damage/duration.

```
Spell Resistance = Building Resistance + (Wizard Ratio × 0.3)
```

**Building Resistance** (primary source):
- Wizard Guilds — primary resistance building, scales with land percentage, capped
- Towers — secondary resistance contribution
- Spires castle improvement — general spell resistance
- Masonry — Lightning Bolt / Arcane Storm specific resistance

Wizard ratio contributes only **30% of its value** to resistance. The majority comes from buildings — land allocation decisions that every playstyle can make equally. An attacker and an explorer with the same Wizard Guild investment have comparable spell resistance regardless of their wizard headcount difference.

#### Success Formula

The success probability uses a **flattened curve** compared to the current system:

```
Success = f(Spell Power / Target Resistance)
```

Key points on the curve:
- **Heavily outmatched (0.5× ratio):** ~10% success (up from ~2-3%)
- **Modestly outmatched (0.8× ratio):** ~35% success (up from ~15-20%)
- **Equal values:** ~55% success (up from ~50%)
- **Modest advantage (1.2× ratio):** ~75% success
- **Heavy advantage (1.5× ratio):** ~90% success
- **Floor:** 3% (up from ~1%)
- **Ceiling:** 97% (unchanged)

The shallower curve means underdogs can still contribute and dominant players don't enjoy near-guaranteed success. The strategic question shifts from "do I have enough ratio" to "is this cast worth the mana."

#### Failure Consequences

Wizard and archmage losses on failure use the same flattened curve — modest mismatches produce modest losses. Severe mismatches still punish heavily to discourage reckless casting.

**Mutual war reduces wizard losses on failure by 25%** (up from 20%), further lowering the cost of wartime aggression.

---

### 2. War Spells: Suppression Instead of Destruction

*Solves: Issue #2 (exponential Fireball damage, Lightning Bolt counterplay, snowball dynamics)*

**Current problem:** Fireball kills peasants, whose recovery is percentage-based — each cast compounds the damage exponentially. Lightning Bolt permanently destroys castle improvement investment with no building-based protection. Burning/Lightning Storm amplify subsequent casts without limit.

**Change:** War spells apply temporary suppression effects with linear recovery.

#### Conflagration (replaces Fireball)

**Effect:** Destroys a portion of food stockpile (instant) AND suppresses platinum and food production for a fixed duration.

- Suppression reduces output by a percentage (tunable, see Open Questions)
- Duration is fixed — ticks down linearly regardless of population count
- Multiple Conflagrations from different casters stack additively up to a cap (e.g., max 50% suppression)
- The target's economy is meaningfully weakened during the window but recovers fully and predictably

**Protection:**
- Wizard Guild buildings reduce the suppression percentage applied (scaling with land %, capped)
- Energy Mirror spell reduces incoming damage and duration
- Spires castle improvement reduces incoming damage
- Technology perks provide additional resistance

**Why this works:** The balance problem with Fireball was exponential recovery. Suppression eliminates this — a 6-hour production reduction is exactly as damaging whether the target has 10,000 peasants or 5,000. The spell is impactful (reduced platinum income during wartime is genuinely threatening) but bounded and predictable. No more "game-ending vs. irrelevant" binary.

#### Arcane Storm (replaces Lightning Bolt)

**Effect:** Suppresses castle improvement effectiveness (Forges, Walls, Keep, Science) for a fixed duration. Improvements are not destroyed — they operate at reduced capacity.

- Suppression reduces improvement bonuses by a percentage
- Duration is fixed, linear recovery
- Tactical use: cast before a coordinated invasion to weaken the target's Forges (OP boost) and Walls (DP boost) during the critical defense window

**Protection:**
- Masonry buildings reduce the effectiveness loss (same building, now protecting against suppression rather than destruction)
- Energy Mirror and Spires provide additional resistance

**Why this works:** Lightning Bolt's counterplay gap is solved — Masonry now has a clear, meaningful defensive role. The spell is arguably more tactically interesting than the original because suppressing Forges/Walls before an invasion creates a coordination window between magic and military, rather than just being standalone economic damage.

#### Status Effects Revised

**Burning** and **Lightning Storm** still amplify subsequent casts of the same type, but:
- **Amplification capped at +50%** (prevents infinite escalation)
- **Status duration: 4 hours** (fixed, not extended by war status)
- Rejuvenation still applies at expiry (forced cooldown between assault cycles)

The assault rhythm becomes: initial cast → 2-3 amplified follow-ups within a tight window → forced Rejuvenation cooldown → cycle resets. Not an unlimited escalation.

**Rejuvenation no longer cancels war declarations.** It suppresses war bonuses (damage amplification, duration extension) while active, but the war state itself is a diplomatic decision that belongs in the realm's hands, not in a spell timer. (This was the v2 critique's #1 priority fix.)

---

### 3. Hostile Spells: Stronger and Dispellable

*Solves: Issue #3 (non-interactive debuffs, marginal impact)*

**Current problem:** Hostile spells are simultaneously too weak to matter and too non-interactive once applied.

**Change:** Increase impact significantly AND add universal dispel (see Section 4). The tradeoff creates a real mana economy: spells are strong enough to justify casting, and dispel is available but costs mana.

#### Revised Effects

| Spell | Duration | Effect |
|---|---|---|
| Plague | 8h | Population growth heavily reduced AND max population temporarily reduced by 5% |
| Insect Swarm | 8h | Food production reduced by 25% AND food decay rate doubled |
| Earthquake | 8h | Gem/ore production reduced by 25% AND construction queue delayed by 1 tick |
| Great Flood | 8h | Boat production halted AND Dock protection capacity halved |
| Disband Spies | Instant | Converts a percentage of target's spies to draftees (unchanged) |

These are now strong enough to demand a response. Earthquake delaying an active construction queue is a real setback. Great Flood halving Dock protection before a naval invasion changes the game. The spells justify their cost because they create meaningful pressure.

But they're also dispellable — so the defender has agency. The interaction becomes: "Do I spend mana to dispel this, or endure it and save mana for my own priorities?" That's a real decision.

---

### 4. Universal Dispel

*Solves: Issue #3 (no counterplay), Issue #4 (role-gated defense), Issue #6 (passive snare)*

**New action available to all players. No role restriction.**

#### Self-Dispel

- **Cost:** 2× the original spell's mana cost
- **Wizard strength cost:** 2 points
- **Always succeeds** — no roll required
- Removes one hostile spell effect immediately

The high mana cost makes it a genuine tradeoff. Dispelling Plague costs significant mana that can't be used for self-buffs or offensive casting. But the option exists — you are never purely passive under a debuff.

#### Allied Dispel

- **Cost:** 1.5× the original spell's mana cost (cheaper than self)
- **Wizard strength cost:** 2 points
- **No role restriction** — any realmmate can do this
- **Always succeeds**

Lower cost than self-dispel incentivizes cooperation. A realm that coordinates dispels is more mana-efficient. This creates meaningful team magic without role gates — any player with mana can help a teammate under magical assault.

#### War Spell Partial Dispel

War spell suppression effects (Conflagration, Arcane Storm) can be partially dispelled:
- Reduces remaining duration by 50% (not full removal)
- **Cost:** 2.5× the war spell's mana cost
- Full removal would trivialize war spells; partial removal gives the defender meaningful agency without negating the attacker's investment

#### Mana Economy Implications

Dispel transforms the magic interaction from a ratio war into a **mana war**. The attacker spends mana to apply debuffs; the defender spends mana to remove them. If the attacker has more mana production, they can force the defender to drain their mana pool on dispels — depleting the defender's ability to maintain self-buffs. Even if every spell gets dispelled, the attacker is winning the mana attrition.

This is a fundamentally different kind of magical pressure. It doesn't require winning the ratio arms race. It requires having mana to burn — which is a function of Tower investment (buildings, equal access) rather than wizard count (population, explorer-favored).

---

### 5. Wards Replace Spell Reflect

*Solves: Issue #5 (Spell Reflect exploitable)*

#### Arcane Ward (Redesigned)

Available to **all players** as a self-spell or castable on allies. No role restriction.

- Provides **3 charges** of spell absorption
- Each incoming hostile or war spell consumes one charge and has its effect **reduced by 40%** (damage, duration, or suppression percentage)
- **Charges are hidden from the attacker** — Revelation reveals that a ward is active but not the remaining charge count
- Duration: 12 hours or until all charges consumed
- Refreshing (recasting) restores charges to full and resets the 12-hour timer

**Why this is better than Spell Reflect:**
- **Can't be scouted and waited out** — hidden charges, 12-hour duration
- **Can't be cheaply probed** — each charge only reduces, doesn't fully negate. Probing with a cheap spell wastes one charge but the remaining two still protect against follow-ups. The attacker pays a real spell to burn each charge.
- **Handles sustained assault** — 3 charges absorb a meaningful campaign, not just one hit
- **No reflection damage** — removes the "worse than doing nothing" critical failure problem

#### Enhanced Ward (Realm Role Benefit)

Grand Magister and Court Mage can cast **Enhanced Wards** on allies:
- 5 charges instead of 3
- 50% reduction instead of 40%
- Same hidden-charge mechanic

Roles provide a clearly superior version but aren't required for basic protection. A realm without role holders can still ward themselves — just less efficiently.

---

### 6. Arcane Fatigue Replaces Snare

*Solves: Issue #6 (passive punishment, zero agency)*

**Current problem:** Below the wizard strength threshold, total lockout. No actions, no help from allies, just waiting.

**Change:** Below the threshold, the dominion enters **Arcane Fatigue** — a degraded state with partial functionality, not total lockout.

#### Partial Casting

- **Self-spells can still be cast** at 2× mana cost and 50% duration
- Hostile, war, and info spells remain locked out
- The player can maintain basic buffs (Ares' Call, Midas Touch, Gaia's Watch) at a premium, preserving their economy and defense during recovery

This eliminates "sit and watch the game for several ticks." The player has meaningful decisions: which self-buffs are worth maintaining at double cost? Can I afford to keep Ares' Call up for defense, or do I need to conserve mana for dispels?

#### Allied Recovery: Arcane Infusion

Any realmmate can cast **Arcane Infusion** to donate wizard strength to a fatigued ally:
- Transfers 5 wizard strength points from helper to recipient
- Costs the helper mana (moderate cost) and 5 wizard strength
- **No role restriction**
- Cooldown: one infusion per helper per target per 2 hours (prevents a single player from instantly refilling an ally)
- Multiple different realmmates can each contribute, enabling coordinated recovery

#### Transparent Recovery UI

- Current wizard strength, recovery rate per tick, and estimated ticks until threshold are displayed explicitly
- Resilience bonus displayed as: "Accelerated recovery: +X/tick from resilience"
- No more hidden mechanic — the player sees exactly what's happening and when it ends

---

### 7. Support Spells for Everyone

*Solves: Issue #4 (role gatekeeping), Issue #8 (realm composition luck)*

**Current problem:** Only Grand Magister and Court Mage can cast friendly spells. Most players cannot contribute to magical team defense. Realms with more magic-focused players have an unearnable structural advantage.

**Change:** Basic support spells available to all players. Roles provide enhanced versions.

#### Available to All Players

| Spell | Target | Cost | Effect |
|---|---|---|---|
| Arcane Ward | Self or ally | Standard mana | 3 charges, 40% effect reduction |
| Allied Dispel | Ally | 1.5× debuff cost | Removes one hostile spell from ally |
| Arcane Infusion | Fatigued ally | Moderate mana + 5 WS | Donates 5 wizard strength |
| Illumination | Self only | Standard mana | Reduces spy operation success against you |

Every player can ward allies, dispel allies, and help fatigued allies recover. A realm of 12 has 12 potential magical defenders, not 2.

#### Enhanced by Realm Roles

| Spell | Role | Effect |
|---|---|---|
| Enhanced Ward | Grand Magister, Court Mage | 5 charges, 50% reduction (ally only) |
| Mass Dispel | Grand Magister | Removes one hostile effect from all realmmates (very high cost) |
| Arcane Shield | Court Mage | Reduces war spell suppression duration on ally by 50% |
| Enhanced Illumination | Court Mage | Stronger spy defense, castable on allies |

Roles amplify and specialize but are not gatekeepers. Losing a Grand Magister hurts (no mass dispel, weaker allied wards) but doesn't eliminate the team's magical defense entirely.

#### Realm Composition Impact

The Issue #8 concern — realms with more magic players dominating — is mitigated because:
- Defensive magic (resistance) is building-based, equal access for all playstyles
- Support spells cost mana, bounded by each player's Tower investment
- The gap between "magic-heavy realm" and "magic-light realm" narrows from "dominant vs. helpless" to "efficient vs. resource-strained"
- A non-magic-focused player can still ward and dispel allies — the spells always succeed, so ratio doesn't matter for support

---

### 8. Cross-System Cascade Fix

*Solves: Issue #1 (cascade across magic/espionage/military)*

The cross-system problem is that winning the wizard ratio lets you cast Disband Spies to degrade the enemy's spy ratio, cascading one advantage into total system control.

**Changes:**

**Disband Spies** is reclassified as a **war operation** (espionage system) rather than a hostile spell (magic system). This means:
- Success is determined by spy ratio, not wizard ratio
- Winning the wizard ratio no longer automatically cascades into espionage dominance
- The espionage system polices itself rather than being undermined by magic

**Spell resistance is building-based**, breaking the population-ratio link for defense. Even if a magic-dominant player can cast offensively, the target's resistance comes from buildings, not wizard count — so the target isn't doubly disadvantaged.

**Invasion momentum gives attackers competitive spell power**, meaning magic specialists can't ignore attackers as magical threats just because attackers have fewer wizards.

The net effect: each competitive system (military, magic, espionage) has more independent defensive axes. Dominating one system still helps, but the cascading "win everything from one advantage" dynamic is significantly weakened.

---

### 9. Late-Round Scaling

*Solves: Issue #7 (magic falls behind military in late round)*

**Current problem:** Military power compounds through elite units, tech, and land growth. Magic power grows only through tech and wonders.

**Changes:**

**Invasion momentum** (Section 1) scales spell power with military activity. As the round intensifies and invasions become more frequent, attacker magic power rises in parallel. Magic doesn't fall behind military — it rides the same activity curve.

**Mastery accumulation broadened:**
- Successful offensive casts: +standard (unchanged)
- **Spells resisted:** +small amount (new — defending against magic builds magical experience)
- **Successful dispels:** +small amount (new — supporting the team builds experience)
- **Ward charges consumed:** +small amount per charge (new — absorbing enemy magic builds experience)

All playstyles now accumulate mastery through engagement with the magic system, not just through offensive dominance. Defensive and support-focused players gain mastery too, ensuring their magical effectiveness scales through the round.

---

## Summary of Changes

| Current Mechanic | Change | Complexity Impact |
|---|---|---|
| Wizard ratio governs offense AND defense | Split into Spell Power (offense) and Spell Resistance (defense, building-based) | Same resources, different formula |
| Success curve (steep exponential) | Flattened curve, higher floor | Number change, same mechanic |
| Fireball kills peasants | Conflagration suppresses production (linear recovery) | Simpler — no population math |
| Lightning Bolt destroys improvements | Arcane Storm suppresses improvement effectiveness | Same target, temporary instead of permanent |
| Burning/Lightning Storm (uncapped escalation) | Capped at +50%, shorter duration | Number change |
| Rejuvenation cancels war | Suppresses war bonuses only | Simpler |
| No dispel | Self-dispel (2× cost) and allied dispel (1.5× cost) | One new action |
| Hostile spells (weak, non-interactive) | Stronger effects + dispellable | Number changes + dispel |
| Spell Reflect (3h, scourable, single-use) | Arcane Ward (12h, 3 hidden charges, 40% reduction) | Replaces 1 spell with 1 spell |
| Snare (total lockout) | Arcane Fatigue (self-spells at 2× cost) | Same resource, different threshold behavior |
| Friendly spells (2 roles only) | Support spells for all; roles get enhanced versions | More players can cast, same spell count |
| Disband Spies (hostile spell) | Reclassified as war operation (espionage system) | Moved, not added |
| Mastery (offense only) | Broadened to defensive/support actions | Same resource, more sources |
| — | Invasion momentum (+8% spell power for 12h after invasion) | One new modifier |

**New terms introduced:** 3 (Spell Power, Spell Resistance, Arcane Fatigue)
**Terms removed:** 2 (Snare, Spell Reflect)
**Net complexity change:** +1 term. No new resource types. No new progression systems.

---

## What This Doesn't Solve

Honesty about limitations:

- **Hostile spells may still feel underwhelming** even with buffed effects, if the mana-war dynamic doesn't create enough pressure. If dispel is too cheap relative to casting cost, hostile spells become "cast → dispelled → wasted." The 2× cost ratio needs playtesting.
- **Realm Auras and passive team contributions** (from v2) are not included here. This redesign gives every player active support tools but no passive "just existing helps the realm" magic. Whether that's needed depends on whether active support is enough.
- **Mana sharing** is not addressed. A teammate with surplus mana cannot donate it to a mana-starved ally (except indirectly via Allied Dispel). The v2 redesign's Ley Shift addressed this but added complexity. Worth considering as a future addition if mana economy proves too siloed.
- **Info spells still produce stale snapshots.** The v2 redesign's Persistent Wards (live-updating intelligence) are a good idea not included here to keep scope minimal. Could be added independently.
- **The 3-day hostile spell restriction** is unchanged. The sudden floodgate on Day 3 is a known UX issue but is more about round structure than magic system design.

---

## Open Tuning Questions

These are parameter decisions that require playtesting:

1. **Suppression percentages** — How much does Conflagration reduce production? 25% for 8 hours? 35% for 6 hours? The economic impact needs modeling against real game states.
2. **Wizard ratio contribution to resistance (0.3 coefficient)** — Too low and wizard investment has no defensive value. Too high and the population advantage reasserts itself. 0.3 means a player with 2× the wizard ratio gets only 60% more resistance from that component, while buildings provide the majority.
3. **Dispel cost ratios** — Self-dispel at 2× and allied at 1.5× the original cost. If too cheap, hostile spells become irrelevant. If too expensive, dispel is inaccessible to non-magic players. These ratios determine whether the mana war is meaningful.
4. **Ward charge count** — 3 charges may be too few against a sustained campaign from multiple casters or too many against a solo attacker. Could scale with Wizard Guild investment (base 2, +1 per 5% Wizard Guilds, capped at 5).
5. **Invasion momentum duration and magnitude** — 12 hours and +8% are starting points. Too short/small and attackers still can't compete magically. Too long/large and attackers become magically dominant post-invasion.
6. **Building Resistance cap** — How much total resistance can buildings provide? If uncapped, heavy Wizard Guild investment makes a dominion nearly spell-immune. Should probably cap at ~60-65% of the maximum effective resistance.
7. **Suppression stacking cap** — Multiple Conflagrations from different casters stacking additively needs a ceiling. Proposed 50% max production loss, but this means 2-3 casters can reach the cap. Is that the right number of casters for maximum pressure?
