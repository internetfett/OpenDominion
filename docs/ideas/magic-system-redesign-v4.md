# Magic System Redesign v4

*Informed by [magic-system-issues.md](magic-system-issues.md) and the current design docs.*

---

## Document Purpose

This document defines a complete redesigned magic system for OpenDominion. It replaces the following subsystems: Fireball, Lightning Bolt, Burning, Lightning Storm, Rejuvenation, all hostile spells (Plague, Insect Swarm, Great Flood, Earthquake, Disband Spies), all friendly spells (Arcane Ward, Illumination, Spell Reflect), and the role gate on friendly spell casting. It redesigns Wizard Guilds (magic function only; building itself is unchanged), Masonry's Lightning Bolt damage reduction, Wizard Mastery, and failure consequences.

The following are explicitly out of scope and unchanged: wizard ratio formula, wizard strength (pool, recovery, tick-based), mana (resource and production), all self spells (generic and racial), Amplify Magic, info spells (Clear Sight, Vision, Revelation, Disclosure), spell duration and recasting, the non-stacking rule, Cyclone, spell duration war bonuses, and the success formula base structure.

---

## 1. Root Cause Analysis

Before prescribing changes, the root causes must be named precisely.

**Root Cause A — Ratio symmetry.** The same stat (wizard ratio) drives both offense and defense. No asymmetry exists: investing in wizards makes you simultaneously harder to hit and better at hitting. This is structurally unwinnable for attackers who must divide population between offensive units, defensive units, and wizards. Explorers only need defense and wizards, so they will always win a wizard investment race.

**Root Cause B — No interactive defense against debuffs.** Hostile spells create locked states with zero recourse for 8+ hours. All counterplay gates on ratio, which is the same race attackers cannot win.

**Root Cause C — Compounding war spell damage.** Fireball's compounding exponential damage (kills peasants whose recovery is %-based) has no bounded floor once started. Burning/Lightning Storm amplify subsequent casts without a cap, making spam the optimal strategy.

**Root Cause D — Friendly spell bottleneck.** Two realm roles gate all team magical defense, creating single points of failure and making 80%+ of realm members passive spectators to magical conflict.

**Root Cause E — Magic does not scale in the late round.** Military compounds via elites, tech, and land. Magic grows only through tech and wonders. Late-round attackers correctly abandon magic investment.

---

## 2. Success Formula — Flat Floor Component

*Solves: Root Cause A (Issue #1)*

**Current problem:** An exponential curve means a heavily outmatched attacker (explorer wizard ratio vs. attacker wizard ratio) faces near-zero success probabilities. Catching up is impossible.

**Change:** After computing the existing ratio-based success chance, blend in a flat component that partially bypasses the ratio gap.

```
successChance = ratioChance × 0.75 + flatFloor × 0.25
```

Where:
- `ratioChance` = the existing exponential function of (caster WPA / target WPA), capped at 0.97, floored at 0.03
- `flatFloor` = 0.35 for hostile spells; 0.25 for war spells (constant, ratio-independent)

**Effect on key scenarios:**

| Ratio Situation | Old Success | New Success (hostile) |
|---|---|---|
| Severely outmatched (0.4× ratio) | ~2–3% | ~10–11% |
| Modestly outmatched (0.7× ratio) | ~20% | ~24% |
| Equal ratio | ~50% | ~46% |
| Modest advantage (1.3× ratio) | ~75% | ~65% |
| Heavy advantage (2× ratio) | ~95% | ~80% |

The curve flattens in both directions: attackers are less punished for being outmatched, but dominant ratios no longer approach guaranteed success. The strategic question shifts from "do I have enough ratio" to "is this cast worth the mana and strength."

**Tuning parameters:**
- `flatFloor` per category (0.35/0.25 are starting points)
- Blend weight (0.25 starting point; range 0.15–0.40)

---

## 3. Failure Consequences — Capped Losses

*Solves: Root Cause A compounding (Issue #1)*

**Current problem:** Wizard/archmage kill rate on failure scales with how badly the caster was outmatched. Heavy ratio disadvantage → severe losses → worse ratio → worse chances. A compounding punishment that further entrenches the arms race.

**Change:** Failure consequences are decoupled from the ratio gap.

- **Wizard kill rate on failure:** Flat 0.5% of total wizards, regardless of ratio differential.
- **Archmage kill rate on failure:** Flat 0.05% of total archmages.
- The existing land-size cap on kills is retained as a secondary cap — whichever is lower applies.

**Effect:** An attacker with poor ratio who attempts to cast against a strong defender loses mana (full cost paid), wizard strength (current mechanic unchanged), and a small predictable number of wizards. Repeated attempts are viable — each cast is a known cost, not a potentially catastrophic loss.

**Tuning parameters:**
- Failure wizard kill rate (0.5% starting point)
- Failure archmage kill rate (0.05% starting point)

---

## 4. Wizard Mastery — Late-Round Scaling

*Solves: Issue #7 (magic falls behind military late round)*

**Current problem:** Mastery accumulates only through successful offensive casts (rewards those already winning) and provides mana cost reduction (marginal). Magic power has no late-round scaling equivalent to military elites.

**Change:** Mastery accumulates through successful offensive casts (unchanged) AND through successful defensive repels. When an incoming hostile spell fails due to the target's wizard ratio resistance, the target gains Mastery proportional to the spell's difficulty.

Offensive cast gain stays at current rate. Defensive repel gain is 40% of the equivalent offensive gain, meaning a purely defensive player reaches roughly 65% of the max attainable Mastery of an aggressive offensive player — meaningful but not equal.

**New Mastery bonuses (replaces mana cost reduction):**

| Mastery | Bonus |
|---|---|
| 0–249 | No bonus |
| 250 | +1% offensive wizard power |
| 500 | +2% offensive wizard power, +5% Wizard Guild mana production |
| 750 | +3% offensive wizard power, +8% Wizard Guild mana production |
| 1000 | +5% offensive wizard power, +10% Wizard Guild mana production, +2% defensive wizard power |

**Effect:** A magic-focused player at 1000 Mastery has +5% offensive wizard power — not enough to close the population gap with explorers, but a real late-round edge that scales with sustained magical engagement throughout the round. Both offensive and defensive players accumulate Mastery, so the system rewards participation rather than just dominance.

---

## 5. Wizard Guilds — Spell Absorption

*Solves: Issue #2 (war spell protection), Issue #3 (no counterplay to hostile spells)*

**Current problem:** Wizard Guilds protect a fixed number of peasants from Fireball. With Fireball redesigned, this function is obsolete. Guilds need a new magic function that rewards building investment rather than wizard ratio.

**New function:** Wizard Guilds provide **hostile spell duration reduction**. Each Wizard Guild reduces the duration of any incoming hostile spell by 0.5 hours when it lands.

```
effective_duration = max(1h, base_duration − floor(guild_count × 0.5))
```

Examples:
- 10 guilds: 8h Plague → 3h
- 20 guilds: 8h Plague → 1h (minimum floor)
- 30+ guilds: always 1h minimum

**Design intent:** This makes Wizard Guild investment the primary mechanism for resisting hostile spell pressure. Attackers must factor in enemy guild counts when deciding whether hostile spells are worth casting. Defenders who invest in guilds — a land allocation decision anyone can make, regardless of playstyle — get meaningful spell resistance without needing to win the wizard ratio race.

The existing Wizard Guild mana production function is unchanged.

**Tuning parameters:**
- Duration reduction per guild (0.5h starting point; range 0.25–0.75h)
- Minimum duration floor (1h)

---

## 6. Fireball — Redesigned War Spell

*Solves: Issue #2 (exponential damage, Burning snowball)*

**What is removed:** Fireball's peasant destruction, food destruction, and Burning status effect. Wizard Guild peasant protection. The Burning status effect entirely.

**New Fireball design:**

Fireball deals three effects:

1. **Immediate:** Destroys food from stockpile — `min(stockpile × 0.08, total_land × 300)` bushels. Bounded by a land-scaled absolute cap to prevent one-shot food wipes.

2. **Duration effect (6 hours):** Reduces food production by 5%. Applied as a DominionSpell effect using existing hostile duration mechanics.

3. **Immediate:** Kills `target.military_draftees × 0.03` draftees.

**Why this works:** Food stockpile damage and draftee loss are bounded, non-compounding effects. A second Fireball reduces an already-depleted stockpile (percentage of remaining food), but food production recovery is not affected by prior losses — it continues at full rate minus the 5% production debuff while active. Two Fireballs are not more than double the damage of one; they are independent, predictable hits. No recovery-rate spiral exists.

Killing draftees creates military pressure (slower unit training pipeline) without catastrophic irreversibility.

**Burning status effect:** Removed entirely. No replacement.

**Tuning parameters:**
- Food stockpile damage percentage (8% starting point)
- Food stockpile absolute cap (land × 300)
- Food production debuff percentage and duration (5%, 6h)
- Draftee kill rate (3%)

---

## 7. Lightning Bolt — Redesigned War Spell

*Solves: Issue #2 (no counterplay equivalent to Fireball, Lightning Storm snowball)*

**What is removed:** Lightning Bolt's Masonry damage reduction interaction. Lightning Storm status effect. Rejuvenation status effect (both Burning and Lightning Storm triggered Rejuvenation; with both removed, Rejuvenation has no triggers and is removed entirely — along with its war-cancellation effect, which removed realm diplomatic agency through a spell timer).

**Masonry redesign:** Masonry retains its castle improvement efficiency bonus (unchanged). Its Lightning Bolt-specific damage reduction is replaced with a **flat war spell damage reduction** that applies to all incoming war spells.

```
masonry_reduction = min(0.20, (masonry_count / total_land) × 0.02)
```

At 10% Masonry land: 20% war spell damage reduction (cap). At 5%: 10%. This makes Masonry a general protective investment, accessible to any playstyle, independent of wizard ratio.

**New Lightning Bolt design:**

- Removes `improvementsVulnerable × 0.04 × damageMultiplier` improvement points across science, keep, forges, and walls (existing proportional distribution logic retained).
- **All damage is temporary:** improvement points are queued to return at tick +12 (12 hours).
- Masonry reduction applies before damage calculation.

**Why this works:** Without Lightning Storm, there is no amplification chain. Each cast deals bounded, predictable, temporary damage. The 12-hour return means rapid successive casts cannot permanently deplete improvements — the first cast's damage begins returning before enough follow-up casts can bottom out a well-invested target. Masonry provides ratio-independent mitigation.

**Lightning Storm status effect:** Removed.

**Rejuvenation status effect:** Removed. No triggers remain.

**Tuning parameters:**
- Improvement damage rate (4% starting point)
- Recovery duration (12 hours)
- Masonry reduction formula (2% per 1% land; 20% cap)

---

## 8. Hostile Spells — Dispel and Rebalanced Effects

*Solves: Issue #3 (non-interactive debuffs, too marginal to matter)*

### 8a. Universal Dispel — Counterspell

A new friendly spell, **Counterspell**, gives any realm member the ability to remove an active hostile spell from an ally.

**Counterspell:**
- Category: `friendly`
- Target: realmmate with an active hostile spell
- Effect: Instantly removes one hostile spell from the target (caster selects which to remove if multiple are active)
- Mana cost: 3× the original casting mana cost of the spell being removed
- Wizard strength cost: 5%
- Minimum requirement: Caster wizard ratio ≥ 0.5 WPA
- No success roll — always succeeds if mana and strength requirements are met
- **No role restriction** (see Section 9)

Counterspell cannot target instant-effect spells (Disband Spies is instant with no duration, so nothing remains to remove). It only targets duration-based hostile spells.

**Why guaranteed success:** Making dispel probabilistic creates a "failed to dispel, defender still stuck" experience that punishes resource expenditure with continued helplessness. Guaranteed success at high cost (3× the spell's mana) creates a clear, legible tradeoff. The interaction becomes a mana war — attacker spends mana to apply debuffs; defender spends mana to remove them. If the attacker has more mana production, they can force the defender to drain their mana pool, even if every hostile spell gets dispelled. The attacker is still winning the attrition.

### 8b. Hostile Spell Rebalancing

With dispel available, hostile spell effects can be meaningfully stronger — they justify casting, and the defender has a genuine choice about whether to spend mana removing them.

| Spell | Duration | Old Effect | New Effect |
|---|---|---|---|
| Plague | 8h | Population growth reduction (~5%) | **−12% population growth** |
| Insect Swarm | 8h | Food production reduction | **−15% food production** |
| Great Flood | 8h | Boat production reduction | **−25% boat production + −5% military sending capacity** |
| Earthquake | 8h | Gem/ore production reduction | **−20% ore production** + gem production reduction unchanged |
| Disband Spies | Instant | Converts % of spies to draftees | **1.5% conversion rate** (reduced) + **24-hour per-target cooldown** |

**Disband Spies rationale:** The cascade described in Issue #1 (winning wizard ratio → Disband Spies → degrade spy ratio → total system control) requires both reducing the conversion rate and limiting cast frequency. The 24-hour per-target cooldown prevents a single magic-dominant player from wiping out an enemy spy corps in one session. The reduction to 1.5% conversion means individual casts have meaningful but not catastrophic impact. Together they break the self-reinforcing cascade without removing the spell's role as a cross-system threat.

---

## 9. Friendly Spells — Open to All, Role-Scaled

*Solves: Issue #4 (role gatekeeping), Issue #5 (Spell Reflect exploitable), Issue #6 (snare passivity), Issue #8 (realm composition luck)*

### 9a. Role Gate Removed

The role check that restricts friendly spell casting to Grand Magister and Court Mage is removed. **Any realm member with wizard strength ≥ 30 (the existing casting floor) and sufficient mana can cast any friendly spell on a realmmate.**

Grand Magister and Court Mage titles are retained for organizational purposes but no longer gate mechanics.

**Stacking concern:** The non-stacking rule (already enforced by the existing system) prevents multiple instances of the same friendly spell from stacking on one target. Only one Arcane Ward can be active per target at a time. Opening friendly spells to all players does not create runaway stacking.

### 9b. Spell Reflect Removed — Replaced by Arcane Aegis

**Current problem:** Spell Reflect's 3-hour duration is scoutable via Revelation, and its single-use design is probe-consumable. An attacker casts one cheap spell to trigger the reflect, then fires the real war spell before recast is possible.

**What is removed:** Spell Reflect entirely.

**New spell — Arcane Aegis:**

- Category: `friendly`
- Duration: 6 hours
- Effect: Incoming hostile or war spells that successfully pass the success roll have a **40% chance to be reflected back to the caster** instead of taking effect on the target. On reflection, the spell resolves against the caster. Unlike old Spell Reflect, Arcane Aegis is **not consumed on use** — it remains active for its full 6-hour duration, with the 40% chance applying to each spell that lands.
- Mana cost: 5× caster total land (higher than most friendly spells)
- Wizard strength cost: 8%
- No role restriction
- Scoutable: Yes, via Revelation. Knowing Aegis is active tells the attacker they face a 40% reflect chance per spell for the full 6 hours.

**Why this fixes the probe exploit:** Old Spell Reflect was deterministic: probe once, reflect consumed, free window opens. Arcane Aegis is stochastic and persistent: even after a spell is reflected, Aegis remains active. The attacker cannot probe it away. They must weigh a 40% self-damage risk on every cast for up to 6 hours. The risk is real enough to discourage casual spell chains, but not absolute enough to make the attacker helpless — they can still cast, they're just accepting a meaningful risk.

**Tuning parameters:**
- Reflection probability (40% starting point; range 25–50%)
- Duration (6 hours)
- Mana cost multiplier (5× starting point)

### 9c. Arcane Ward — Unchanged, Opened to All

Arcane Ward (reduces hostile spell success chance on ally) is functionally unchanged. Role gate removed — any realm member can cast it on any realmmate. Duration stays at 6 hours.

The `enemy_spell_chance` perk value should be reviewed upward (toward −12% to −15%) since Wizard Guild duration absorption (Section 5) now handles the physical duration layer. Arcane Ward covers success probability; guilds cover duration — two independent defensive axes.

### 9d. Illumination — Unchanged, Opened to All

Illumination (reduces spy operation success on ally) is functionally unchanged. Role gate removed. Duration stays at 6 hours.

### 9e. Counterspell (new — defined in Section 8a)

Any realm member with 0.5+ WPA can cast Counterspell.

### 9f. Bolster (new) — Snare Counterplay

*Solves: Issue #6 (snare is pure passive punishment)*

**Current problem:** Below the wizard strength threshold, a dominion is in total lockout. No spells can be cast. No ally can help. The player waits passively for recovery.

**New spell — Bolster:**

- Category: `friendly`
- Target: realmmate (any, including snared realmmates)
- Effect: Transfers 5 wizard strength points from caster to target. If the target is below the casting floor, the donation can bring them above it and restore spell access.
- Mana cost: 1× caster total land (low — utility spell)
- Wizard strength cost: 8% of caster's current strength (the caster donates from their own pool)
- No success roll — always succeeds if requirements met
- Cap: Target cannot exceed 100 wizard strength through Bolster (excess wasted)
- Cooldown: 6 hours per caster per target

**Effect:** Multiple different realmmates can each Bolster a snared ally within the same hour, collectively restoring meaningful wizard strength quickly. A coordinated realm can un-snare a key member within one tick. The Snare threat remains — a sustained Snare campaign against an active realm provokes coordinated Bolster responses, and the attacker must increase Snare intensity to overcome allied recovery.

---

## 10. Summary of All Changes

### Spells Modified

| Spell | Type | Change |
|---|---|---|
| Fireball | War | Stockpile food damage (8%, land-capped) + food production −5%/6h + kills 3% draftees. No peasant kills. |
| Lightning Bolt | War | All damage temporary (12h return). Masonry provides flat war spell mitigation (2% per 1% land, 20% cap). |
| Plague | Hostile | Effect increased to −12% population growth |
| Insect Swarm | Hostile | Effect increased to −15% food production |
| Great Flood | Hostile | Effect increased to −25% boat production + −5% military sending capacity |
| Earthquake | Hostile | Ore production penalty increased to −20% |
| Disband Spies | Hostile | Conversion rate reduced to 1.5%; 24-hour per-target cooldown added |
| Arcane Ward | Friendly | Role gate removed; perk value tuned upward toward −12–15% |
| Illumination | Friendly | Role gate removed |
| Spell Reflect | Friendly | **Removed** |

### Spells Added

| Spell | Type | Effect |
|---|---|---|
| Arcane Aegis | Friendly | 6h; 40% chance per incoming spell to reflect back to caster; not consumed on reflection |
| Counterspell | Friendly | Instant; removes one active hostile spell from target at 3× its mana cost; 0.5 WPA minimum |
| Bolster | Friendly | Transfers 5 wizard strength to ally; 8% strength cost; 6h cooldown per target |

### Status Effects Removed

| Effect | Reason |
|---|---|
| Burning | Fireball redesigned; no trigger |
| Lightning Storm | Lightning Bolt redesigned; no trigger |
| Rejuvenation | Both source triggers removed; also removes the war-cancellation side effect |

### Mechanics Changed

| Mechanic | Change |
|---|---|
| Success formula | Flat floor component (25% weight, 35%/25% hostile/war) blended into ratio-based result |
| Failure wizard losses | Capped at flat 0.5% of wizards regardless of ratio gap |
| Wizard Guilds magic function | Peasant protection removed; replaced with hostile spell duration reduction (0.5h per guild, 1h minimum) |
| Masonry magic function | Lightning Bolt-specific reduction removed; replaced with flat war spell mitigation (2% per 1% land, 20% cap) |
| Wizard Mastery accumulation | Defensive repels now grant mastery at 40% of offensive rate |
| Wizard Mastery bonuses | Mana cost reduction removed; replaced with tiered wizard power and guild mana production bonuses |
| Friendly spell role gate | Removed; any realm member with 30+ wizard strength can cast friendly spells on realmmates |

---

## 11. Issues Addressed

| Issue | Priority | Resolution |
|---|---|---|
| Ratio arms race unwinnable for attackers (#1) | Critical | Success formula flat floor; failure loss cap; Mastery now accumulates defensively; building-based spell resistance via Wizard Guilds |
| Lightning Bolt has no counterplay (#2) | Critical | Lightning Bolt damage made temporary (12h return); Masonry provides flat ratio-independent war spell mitigation |
| No dispel — defenders passive under debuffs (#3) | Critical | Counterspell (any realmmate, always succeeds, 3× mana cost); Wizard Guild duration absorption reduces debuff windows |
| Friendly spells restricted to 2 realm roles (#4) | Critical | Role gate removed entirely; all friendly spells open to any realmmate with 30+ wizard strength |
| Burning/Lightning Storm snowball dynamics (#2) | High | Both status effects removed; no amplification chain exists |
| Spell Reflect trivially scouted and probed (#5) | High | Spell Reflect removed; Arcane Aegis replaces it with persistent probabilistic reflection that cannot be probed away |
| Snare offers zero agency (#6) | Medium | Bolster friendly spell allows any realmmate to donate wizard strength to a snared ally |
| Late-round magic falls behind military (#7) | Medium | Wizard Mastery redesigned with tiered power bonuses at high mastery thresholds |
| Realm magic strength is luck of the draw (#8) | Medium | Building-based defenses (Wizard Guilds, Masonry) are accessible to any playstyle; Counterspell and Bolster available to all players regardless of magic investment |

---

## 12. Tuning Parameter Reference

| Parameter | Starting Value | Adjust Up If | Adjust Down If |
|---|---|---|---|
| Success flatFloor (hostile) | 0.35 | Attackers still cannot land spells | Explorers overwhelmed by hostile spam |
| Success flatFloor (war) | 0.25 | War spells never land on defenders | War spells trivially land on everyone |
| FlatFloor blend weight | 0.25 | Ratio still dominates completely | Ratio feels irrelevant |
| Failure wizard kill rate | 0.5% | Attackers still hemorrhage wizards on attempts | Failed casts feel consequence-free |
| Guild duration absorption | 0.5h/guild | Hostile spells always expire before mattering | Heavy guild investment = total spell immunity |
| Guild minimum duration floor | 1h | Guilds completely negate hostile spells | No investment can reduce duration meaningfully |
| Masonry war mitigation cap | 20% | Lightning Bolt does no damage to Masonry-invested targets | Masonry is ignored in Lightning Bolt calculus |
| Fireball food stockpile % | 8% | Fireball feels pointless | One cast destroys the food economy |
| Fireball food absolute cap | land × 300 | Fireball harmless against large targets | Fireball one-shots food on small targets |
| Fireball draftee kill rate | 3% | No military pressure from Fireball | Fireball blocks military training completely |
| Lightning Bolt improvement % | 4% | Lightning Bolt feels irrelevant | Improvements permanently depleted quickly |
| Lightning Bolt recovery time | 12h | Improvements destroyed faster than they recover | Spell is trivially recovered from |
| Plague magnitude | −12% | Plague still ignored | Plague alone defeats a dominion |
| Insect Swarm magnitude | −15% | Swarm still ignored | Food production destroyed in hours |
| Great Flood boat magnitude | −25% | Great Flood still ignored | Naval invasions impossible while flooded |
| Great Flood capacity penalty | −5% | Great Flood too niche | Great Flood cripples any attacker |
| Earthquake ore magnitude | −20% | Earthquake still ignored | Ore-heavy races unable to train units while Quaked |
| Disband Spies rate | 1.5% | Spy corps never threatened | Spy corps wiped in a single session |
| Disband Spies cooldown | 24h | Repeated casts still cascade | Disband Spies too infrequent to matter |
| Arcane Aegis reflect chance | 40% | Defenders never get a reflect benefit | Attackers never want to cast with Aegis active |
| Counterspell mana multiplier | 3× | Counterspell used too freely | Counterspell never used due to cost |
| Bolster strength transfer | 5 points | Snared players still stuck for multiple ticks | Snare trivially countered with one ally |
| Bolster cooldown | 6h per target | One ally can repeatedly unsnare without cost | Bolster feels too restricted to matter |
| Mastery tier 4 bonus | +5% offense WP | Late-round magic still irrelevant | Mastery players dominate mid-round |
| Arcane Ward perk value | −12–15% | Ward does nothing against skilled casters | Ward makes skilled casting impossible |
