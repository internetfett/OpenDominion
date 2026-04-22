# Magic System — Consolidated Ideas

*Synthesized from: magic-redesign.md, magic-leylines.md, magic-system-redesign.md, magic-system-redesign-v2.md, magic-system-redesign-v2-critique.md, magic-system-redesign-v3.md, magic-system-redesign-v4.md, magic5.md*

---

## 1. Split Offense and Defense Into Independent Stats

**The core structural change proposed in every redesign document.** Currently wizard ratio drives both spell success (offense) and spell resistance (defense), creating an arms race non-wizard builds can never win.

**Proposed approach (consensus across all docs):**

- **Offensive stat** (variously named: Arcane Power, Offensive Weave, Spell Power) — derived from wizard units. Formula is the existing wizard ratio, unchanged, now applying only to offense.
- **Defensive stat** (variously named: Arcane Resilience, Ward Power, Ward Rating, Spell Resistance) — derived primarily from *buildings*, not units. Wizard Guilds, Towers, Masonry, and Spires all contribute. Wizard count contributes only 25–30% of its current value (or nothing at all, depending on the variant).

**Effect:** A militarized dominion that invests in Wizard Guilds and Masonry has meaningful magical defense without training a single wizard. The arms race branches — attackers invest in wizards, defenders invest in buildings. Neither dominates the other.

**Unit-based allocation options (new):**

- **Option A — Role differentiation by unit type:** Wizards contribute only to Spell Power (offense). Archmages contribute only to Ward Rating (defense). The training decision *is* the allocation decision — no new UI or concepts required. Replaces the current "archmages count as 2 wizards" formula with two separate formulas. Archmages shift from feeling like "upgraded wizards" to being the dedicated defensive unit.

- **Option B — Posture dial:** A 0–100 setting (like draft rate) that splits the combined wizard/archmage pool between Spell Power and Ward Rating. Setting it to 70 means 70% of total wizard ratio drives offense, 30% drives defense. Changes by 1% per hour toward the target (same mechanic as draft rate) — prevents instant reallocation exploits while keeping the system responsive. Changeable at any time; the lag creates a meaningful commitment window.

**Variants on the defensive formula:**
- Simple: `Building Resistance + (Wizard Ratio × 0.3)` (v3)
- Building-only: `(Wizard Guild % × 3.0) + (Tower % × 0.8) + Spires` (leylines, v5)
- Separate units: Archmages become the dedicated defensive unit, not contributing to offense at all (v2, redesign.md)

**Success formula:** Becomes `f(Spell Power / Ward Power)` with the same exponential curve shape. A flat floor component (25% weight toward a ratio-independent baseline of 35% for hostile, 25% for war) flattens the curve so underdogs can still cast (v4).

**Failure consequences:** Wizard kill rate on failure decoupled from the ratio gap — flat 0.5% of total wizards regardless of mismatch severity. Repeated attempts become a known cost, not a potentially catastrophic loss (v4). Alternatively: no wizard death at all, replaced by a temporary Spell Power penalty (Arcane Overload) that recovers passively (v2, redesign.md).

---

## 2. War Spell Redesign

**Fireball and Lightning Bolt both have problems. Multiple approaches exist.**

### Fireball

**Problem:** Peasant death creates exponential recovery dynamics — each cast compounds the damage because population recovery is percentage-based.

**Option A — Suppression-based (v3):** Fireball becomes *Conflagration*. Destroys food stockpile (instant) and suppresses platinum and food production for a fixed duration. No peasant death. Multiple casts from different casters stack additively up to a cap (e.g., 50% max suppression). Linear, bounded, predictable.

**Option B — Bounded food + draftees (v4):** Fireball destroys a percentage of food stockpile (8%, with an absolute land-scaled cap to prevent one-shot wipes), reduces food production by 5% for 6 hours, and kills 3% of draftees. Peasant death removed. Two independent, non-compounding hits.

**Option C — Blight Stacks (redesign.md, v2):** Fireball remains peasant-based but is folded into the Devastation School framework. Wizard Guild protection retained. War Mage exclusive spell (Inferno) provides a once-per-72h mega-Fireball.

### Lightning Bolt

**Problem:** Has no building-based counterplay equivalent to Wizard Guild protection for Fireball.

**Option A — Masonry parity (v3, v4, v5):** Masonry provides equivalent protection to Lightning Bolt that Wizard Guilds provide to Fireball. Each percentage point of Masonry land contributes the same marginal protection. Cap at 50% (matching Fireball cap).

**Option B — Temporary damage (v4):** All Lightning Bolt damage is temporary — improvement points are queued to return at tick +12. No permanent destruction. Masonry provides flat war spell mitigation (2% per 1% land, 20% cap).

**Self-buff counterplay — Arcane Hardening (v5):** A new self-spell providing flat Lightning Bolt damage reduction, independent of Masonry. Stacks additively with Masonry up to the hard cap. Gives Lightning Bolt the ratio-independent counterplay layer that Fireball has through Wizard Guilds.

### Status Effects (Burning / Lightning Storm)

**Problem:** Amplify subsequent casts without a cap, incentivizing spam and creating snowball dynamics.

**Resolution (consensus v3, v4, v5):**
- Cap amplification at +50% flat (not compounding). No infinite escalation.
- Status duration fixed at 4 hours, not extended by war status.
- A second same-type cast before expiry extends duration partially rather than stacking more damage.
- Strategic incentive shifts from "spam to compound" to "apply and sustain."

**Rejuvenation:** Removed entirely in v4 (because both triggers — Burning and Lightning Storm — are removed). In v3 and v5, Rejuvenation is redesigned to suppress war bonuses (damage amplification, duration extension) while active. Note: Rejuvenation is cancelled by a new war declaration (not the reverse) — an attacker can strip a target's Rejuvenation protection by declaring war, which is worth accounting for in any redesign.

---

## 3. Hostile Spell Improvements

**All redesign documents agree: hostile spells need to be both stronger and more interactive.**

### Stronger Effects

With dispel/cleanse available as a response (see Section 4), hostile spells can be meaningfully impactful without feeling oppressive. Current debuffs are too weak to justify casting; with the ability to fight back, they can hurt enough that fighting back is worth the effort.

Suggested rebalanced values (v4):
- Plague: −12% population growth (up from ~5%)
- Insect Swarm: −15% food production
- Great Flood: −25% boat production + −5% military sending capacity
- Earthquake: −20% ore production

### Wizard Guild Duration Reduction (v4)

Wizard Guilds shift from protecting peasants against Fireball (obsolete if Fireball is redesigned) to reducing the duration of incoming hostile spells.

Formula: `effective_duration = max(1h, base_duration − floor(guild_count × 0.5h))`

At 10 guilds, 8h Plague becomes 3h. At 20 guilds, it becomes 1h. This makes guild investment the primary mechanism for resisting hostile spell pressure — a building decision anyone can make, independent of wizard ratio.

### Blight Stacks (redesign.md, v2, leylines)

Hostile spells apply stackable debuffs (Blight Stacks) rather than single-instance effects. Maximum 3 stacks per type per target. Each stack decays 1 per tick; maintaining max stacks requires casting once per tick minimum.

Each stack increases the debuff's magnitude (e.g., +15% per stack, so 3 stacks = 45% stronger than base).

**Sustained pressure reward:** A mage who has consistently maintained a debuff across three recast cycles applies a meaningfully stronger effect than one who just landed it. Defenders have specific reason to break the pressure via dispel/ward rather than passively absorbing debuffs.

**Countermeasures (redesign.md):** Spell Reflect/Arcane Ward can strip stacks. Arcane Ward prevents new stacks from forming during its duration.

**Simplification option (v2-critique):** Instead of 5 independent blight types with separate decay clocks, use a unified Ruin Blight pool per target (max 5 stacks). Different spells emphasize different debuff effects per stack but share a stack counter. Reduces bookkeeping without removing the sustained-pressure mechanic.

### Attunement Stacks (magic-redesign.md)

An alternative or complement to Blight Stacks. Each time a hostile spell is recast on the same target, the caster gains 1 Attunement Stack for that spell (max 3). Stacks increase effectiveness by +15% each. Lost if the spell expires before being recast — pressure must be maintained to hold the advantage.

---

## 4. Dispel / Counterspell Mechanics

**All redesign documents agree defenders need active response options.** Multiple approaches exist:

### Cleanse — Ward-Power-Based Self-Dispel (leylines, v5)

A self-cast reactive spell that removes one active hostile debuff. Success based on Ward Power vs. a fixed potency value per spell type. Higher Ward Power = more reliable cleanse. Cooldown: cannot cleanse the same spell type twice within one duration window (prevents trivially nullifying all pressure — each cleanse clears one debuff then pauses).

Cannot be used while snared/depleted.

### Universal Dispel — Always-Succeeds at Mana Cost (v3, v4)

**Self-Dispel:** Always succeeds. Costs 2–3× the original spell's mana cost. No success roll.

**Allied Dispel:** Any realmmate can cast on an ally. Costs 1.5× the original spell's mana cost. Always succeeds. No role restriction.

**War Spell Partial Dispel:** Reduces remaining suppression duration by 50% (not full removal). Costs 2.5× the war spell's mana cost.

**Mana war dynamic:** Dispel transforms magical conflict into mana attrition. Even if every spell gets dispelled, the attacker who has more mana production wins — they force the defender to drain their pool on dispels, depleting their ability to maintain self-buffs. Winning the ratio arms race is no longer required.

### Counterspell Window — Active Response Options (redesign.md)

When any hostile or war spell lands, the defender gains a 4-hour Counterspell Window with tiered response options:

| Option | Effect | Cost | Ward Rating Requirement |
|---|---|---|---|
| Absorb | Accept full effect; generate mana equal to 20% of spell's cost | Free | None |
| Diminish | Reduce potency and duration by 40% | 1.5× spell cost | None |
| Dispel | Remove effect entirely | 3× spell cost | Ward Rating ≥ 50% attacker Spell Power |
| Resist | Reduce by 60%, recover 50% of mana cost | 2× spell cost | Ward Rating ≥ 75% attacker Spell Power |

If the window expires without action, the spell takes full effect. This means even non-magic players can pay a premium to take reduced hits (Diminish always available), while magic-invested players get efficient removal (Resist).

### Stance System — Pre-Configured Automatic Response (v2)

Replaces reactive counterspelling. Players configure their defensive posture in advance. Stances trigger **automatically and immediately** when a spell lands, regardless of whether the player is online.

| Stance | Effect | Ward Charge Cost |
|---|---|---|
| Absorb | Accept full effect; generate mana at 15% of spell's cost | 0 |
| Diminish | Reduce effect by 40% | 1 |
| Dispel | Remove effect entirely | 3 |

Ward Charges are generated passively by Archmages and Wizard Guilds. Against sustained assault, charges eventually run dry and stance falls back to Absorb. The offline player's wards still work automatically; online players get a 60-second override window with access to a superior **Resist** option (60% reduction, recovers 1 Ward Charge).

---

## 5. Spell Reflect Replacement

**Spell Reflect has two problems: its 3-hour duration is scoutable via Revelation, and it can be probe-consumed cheaply before the real attack.**

### Arcane Bulwark (v5, leylines)

Replaces Spell Reflect entirely.

- Multi-charge (base: 4–6 charges)
- Provides a **percentage chance to resist** each incoming hostile or war spell (not single-use block)
- **Not visible to Revelation** — attackers cannot scout whether it is active
- Each successful deflection consumes a charge; when all charges depleted, it expires
- Duration: 8 hours base
- Recasting before expiry replenishes charges without resetting timer
- Role-restricted (Grand Magister / Court Mage) — roles remain desirable without being the only defensive option

**Why this fixes the probe exploit:** Old Spell Reflect was deterministic — probe once, Reflect consumed, window opens. Arcane Bulwark is stochastic and persistent. Even after a spell is reflected, it remains active. The attacker cannot probe it away and cannot scout it. They must accept a meaningful risk per cast for up to 8 hours.

### Arcane Aegis (v4)

Alternative: persistent 40% per-spell reflection chance for 6 hours. Not consumed on use. Scoutable via Revelation (the attacker knows the risk but cannot eliminate it). Higher cost than normal friendly spells.

### Sanctum Shield (redesign.md, v2)

Full negation of the next hostile or war spell on a realmmate. Does not reflect — purely absorbs. 6-hour duration (refreshable before consumed). Only the Grandmaster Warden specialization gets a limited reflection component (25% power back), and it is non-amplified.

---

## 6. Snare / Depletion Redesign

**Below the wizard strength threshold, the current system is total lockout with no agency.**

### Arcane Fatigue — Partial Casting (v3)

Below threshold, the dominion enters Arcane Fatigue: a degraded state, not total lockout.

- Self-spells can still be cast at 2× mana cost and 50% duration
- Hostile, war, and info spells remain locked
- The player can maintain basic buffs (Ares' Call, Midas Touch) at a premium, preserving economy and defense during recovery

### Arcane Depletion — Self-Spells Always Available (leylines)

Self-spells remain castable while depleted, but cost 2× Resonance and each cast costs an additional 5 Wizard Strength. Ley Threads to the realm reservoir continue flowing — the depleted player still contributes to the team.

Emergency Resonance conversion: spend 80 Resonance to recover 5 Wizard Strength. Up to 3 uses per depletion event. Expensive, limited, last resort.

### Emergency Ward (v5)

A self-cast reactive spell that functions while snared. Spends a significant mana reserve to restore a fixed amount of wizard strength, bypassing the threshold. Long cooldown, cannot be cast again until full recovery. Last resort only.

### Allied Recovery Spells (all redesign docs agree)

Any realmmate can help a depleted ally. Named differently across documents:
- **Bolster** (v4) — Transfers 5 wizard strength from caster to target. 8% caster strength cost. 6-hour cooldown per target. Any realm member can cast.
- **Channel Mana / Arcane Infusion** (v5, v3) — Partial transfer with an inefficiency cost (caster loses more than target gains). Cannot be cast if caster is near their own threshold.
- **Ley Infusion** (leylines) — Any realm member can cast. Transfers 10% strength to target at 15% cost to caster. 4-tick cooldown between casts on the same target.

Multiple different realmmates can each contribute, enabling coordinated recovery. A coordinated realm can un-snare a key member within one tick. The snare threat remains — but it's now a social mechanic where your realm can help at real cost.

### Transparent UI

Resilience (accelerated recovery while snared) displayed explicitly in the UI — the hidden acceleration is no longer secret. Shows current wizard strength, recovery rate per tick, and estimated ticks until threshold.

---

## 7. Friendly Spells — Open to All Realm Members

**All redesign documents after v1 agree: friendly spells should not be gated behind two realm roles.**

**Current problem:** Only Grand Magister and Court Mage can cast friendly spells. 80%+ of realm members are passive spectators to magical conflict. If either role holder goes inactive, the realm's magical defense collapses.

**Proposed change:** Basic friendly spells (Arcane Ward, Illumination) available to any realm member. Grand Magister / Court Mage cast enhanced versions — stronger effect, lower mana cost — but are no longer the only option.

Tiered access example (v5):
- Any realm member: Arcane Ward at normal strength scaling with caster's Ward Power
- Court Mage: 1.4× effect strength, 0.8× mana cost
- Grand Magister: 1.6× effect strength, 0.75× mana cost, exclusive access to Arcane Bulwark

**Non-stacking prevention:** The existing non-stacking rule prevents runaway stacking. Only one Arcane Ward active per target at a time, regardless of who cast it.

**New universal support spells:**
- Allied Dispel / Counterspell — any realmmate can remove a hostile debuff from an ally
- Arcane Infusion / Bolster / Channel Mana — any realmmate can help a snared ally recover

---

## 8. Specialization / Magical Identity

**Multiple documents propose letting players commit to a magical archetype that introduces distinct playstyles and in-game decisions. Complexity varies significantly across proposals.**

### Arcane Doctrine — Simple Specialization (magic-redesign.md)

Chosen once per round during dominion creation. A specialization layered on top of race abilities — not a replacement.

| Doctrine | Bonuses | Penalties |
|---|---|---|
| Warmage | War spell damage +25% | Hostile spell duration −25%, Wizard Strength recovery −10% |
| Hexmaster | Hostile success rate +10%, duration +25%, Attunement cap raised to 4 | War spell damage −20% |
| Wardcaster | Arcane Resilience +50%, Brace +50% stronger | Offensive AP −20% |
| Runesmith | Self spell cost −40%, duration +30%, mana production +15% | Cannot cast war spells |
| Arcanist (default) | No bonuses or penalties | Full spell access |

### Arcane Schools — Medium Complexity (leylines)

Each school has a passive, a unique spell, and a Ritual contribution bonus. Independent of race.

- **Evocation** — War spell damage +15%, unique spell suppresses terrain-based perks, Ritual combat weighting
- **Abjuration** — Ward Power +50% per guild, unique spell blocks recasting of one self-spell, Ritual defense weighting
- **Conjuration** — Ley Thread contribution doubled, unique spells: Mana Siphon (drains enemy Resonance into realm pool) and Thrall Weave (converts Ley Charge to draftees)
- **Divination** — Info spells never fail, hostile duration +25%, unique spells: Weave Reading (deep intelligence) and Entropy Hex (halves one active self-buff's bonus)
- **Unaffiliated (default)** — Full generic access, no bonuses

### Mastery Paths — High Complexity (redesign.md, v2)

Chosen by Day 7 (or Day 14 per v2-critique recommendation). Permanent for the round. Grants Grandmaster attunement in one school, 2 unique spells, and a **Realm Aura** — a passive bonus applied to all realm members, no title required.

| Path | School | Unique Spells | Realm Aura |
|---|---|---|---|
| Arcanist | Adept all schools | Ley Shift (mana transfer), Arcane Audit (reads target's attunement) | 5–8% mana cost reduction for all members |
| War Mage | Grandmaster Devastation | Inferno (2× Fireball, bypasses protection), Siege Mark (+15% realm damage vs. target) | +5% offensive military power during war |
| Hexblade | Grandmaster Ruin | Withering Pall (doubles active Blight Stacks), Hex Chain (next realm cast applies +1 stack) | All realm Blight Stacks decay 1 tick later |
| Seer | Grandmaster Unseen | Fate Sight (Persistent Ward on entire enemy realm), Reveal Weakness (exact OP/DP readout) | All Persistent Wards update every 30 minutes |
| Warden | Grandmaster Warding | Grand Sanctum (Sanctum Shield all realm members), Arcane Retribution (grants Resist access to ally) | Diminish Stance effectiveness raised to 50% for all members |

**Complexity note (v2-critique):** The 4-tier attunement system (Novice/Adept/Master/Grandmaster) creates a 4×4 effectiveness matrix applied to every spell. Suggested simplification: two tiers only (Standard and Attuned). Attuned grants a single clear bonus per school. This retains differentiation without the opacity.

---

## 9. Realm Teamwork Mechanics

**Two distinct approaches exist for enabling coordinated realm magic.**

### Ritual System — Asynchronous Cooperative Casting (leylines)

Rituals are cooperative casting events where multiple realm members contribute to a single powerful effect over 8 hours. Designed to work across timezones — no simultaneous presence required.

1. A realm leader (Magister role or above) initiates a Ritual and commits initial Ley Charge.
2. For the next 8 hours, any realm member can contribute Ley Charge or Resonance.
3. Total contributions determine the Ritual's tier (Flicker/Stable/Resonant/Ascendant).
4. At 8 hours (or manual trigger after 4 hours), the Ritual fires.

| Tier | Contribution | Effect Multiplier |
|---|---|---|
| Flicker | Minimum | 0.5× base |
| Stable | Moderate | 1.0× |
| Resonant | Strong | 1.5× |
| Ascendant | Maximum | 2.5× |

Example Rituals:
- **Veil of Ashes** (Defensive) — Reduces incoming hostile spell success for all realm members by 10–45% for 12 hours.
- **Arcane Reckoning** (Offensive) — Simultaneously strikes 3 enemy dominions with Plague + Insect Swarm. Requires active war.
- **Shattered Aegis** (Offensive) — Temporarily reduces target's Ward Power by 30% for 6 hours. Opens an attack window.
- **Convergence Ward** (Defensive) — Absorbs the next 1–3 incoming war spells against a chosen realmmate.
- **Ley Beacon** (Intelligence) — Reveals Ley Flux and active Rituals in a chosen enemy realm for 24 hours.

Contributors above a minimum threshold earn Wizard Mastery and ranking credit.

### Arcane Congress — Synchronous Pool Casting (redesign.md, v2)

Once per 72 hours, any Mastery Path holder can call an Arcane Congress. All realm members have a 4-hour window (suggested extension to 12 hours per v2-critique) to contribute mana to a shared pool. The caller then selects from available spells based on total contribution.

| Pool Threshold | Spell | Effect |
|---|---|---|
| 500+ | Ley Blessing | All realm members +15% mana production for 24h |
| 1,500+ | Arcane Bulwark | All realm members +20% Ward Rating for 24h |
| 3,000+ | Siege Confluence | All Devastation spells vs. target +30% damage for 12h |
| 6,000+ | Grand Warding | Sanctum Shield + Arcane Ward on every realm member, 6h |
| 10,000+ | Apocalypse Confluence | 5× Fireball + 5× Lightning Bolt on designated target, ignores 50% WR. Once per round. Requires mutual war. |

Any realm member can contribute regardless of title or path. Every Congress call is a real strategic decision — the 72h cooldown makes timing matter.

### Shared Mana (Multiple Docs)

- **Ley Shift** (Arcanist path) — Transfer up to 25% of current mana to a realmmate. Once per 24 hours. Individual mana sharing.
- **Ley Reservoir** (leylines) — A realm-wide mana pool fed by Wizard Guild land percentage from all members. Used for Rituals and war spells. Any member can draw from it.
- **Mana Siphon** (leylines, Conjuration) — Drains Resonance from an enemy and adds it to the realm's Ley Reservoir (not to the caster — team resource).

---

## 10. Intelligence and Information

**Magic intelligence currently produces stale snapshots. Better information creates interesting decisions.**

### Persistent Wards (redesign.md, v2, v5)

When an info spell is cast with a Surge Charge (Empowered), it creates a Persistent Ward instead of a snapshot. A Persistent Ward updates every tick for its duration (12–18 hours), providing live intelligence on a target's military, active spells, incoming land, and Blight Stack counts.

A realm with active Persistent Wards on high-priority targets has genuinely actionable intelligence — current information when an invasion window opens, not data from hours ago.

Upgrade path: Seer Mastery Path's Fate Sight creates Persistent Wards on an entire enemy realm simultaneously with a single cast.

### Arcane Forecast (magic-redesign.md)

A cheap, always-succeeds information action (no roll, no Wizard Strength cost):
- Reveals whether the target has any Attunement Stacks accumulating against your dominion
- Reveals whether target's Wizard Strength is above or below 70
- Does not reveal spell identity, stack count, or exact Wizard Strength

Paired with **Spell Preparation**: a caster may optionally spend 2 ticks "charging" a war spell (paying 50% mana cost over the window). After completion, the next cast deals +35% damage and costs 25% less Wizard Strength — but the preparation is visible to Arcane Forecast. Creates a deliberate tradeoff: more damage but target gets a warning window to Brace or respond.

### Ley Beacon / Weave Reading (leylines)

**Ley Beacon** (Ritual): For 24 hours, reveals the Ley Flux level of every dominion in a chosen enemy realm, plus any active Rituals they are running. Pairs naturally with intelligence-focused players.

**Weave Reading** (Divination spell): Reveals the target's current Ley Flux, active Rituals in their realm, and whether any realm members are currently Arcane Depleted.

---

## 11. Anti-Pile-On and Protection Mechanics

**Multiple approaches exist to prevent sustained magical bombardment from overwhelming defenceless targets.**

### War Footing Protection (magic-redesign.md)

Passive state applied automatically while a dominion has troops out on an invasion.
- AR bonus: +30% (remaining home forces increase defensive vigilance)
- Brace effect: +50% stronger while active
- Does not affect offensive Spell Power
- Expires when troops return home

Removes the "I'll wait for you to invade someone and then Fireball you" counterplay. An attacker who commits to an invasion retains meaningful magical defense.

**Proportional Defense (always active):** Dominions below 75% of average round land size receive a passive AR bonus: `(1 − size_fraction) × 20%`, capped at +20%. Prevents pile-on scenarios where multiple large casters hammer a small target into oblivion.

### Arcane Saturation (v2)

Tracks the total number of hostile and war spells a dominion has received from a single source realm within a rolling 24-hour window. Provides escalating resistance.

| Level | Threshold | Effect |
|---|---|---|
| None | 0–3 spells | No change |
| Low | 4–6 spells | Diminish Stance costs 1 fewer Ward Charge from that realm |
| Medium | 7–10 spells | Blight Stacks from that realm decay 1 extra per tick |
| High | 11–14 spells | Stacks decay 2 extra/tick; war spell damage −20% |
| Arcane Immunity | 15+ spells | All spells from that realm auto-fail for 12 hours |

Tracked per source realm, not per individual caster. Rotating casters does not circumvent accumulation.

**Fix from v2-critique:** After Immunity expires, Saturation resets to Low (not Zero). Full reset requires 24 hours with no incoming spells from that realm. Prevents coordinated attackers from treating immunity as a predictable clear window.

### Ley Flux (leylines)

Per-dominion score measuring magical punishment recently absorbed. Rises when hit, decays 4 per tick during peace.

| Flux | Effect |
|---|---|
| 0–20 | Normal state |
| 21–45 | Battle-Wary: −8% incoming success chance; Ley Thread contribution +15% |
| 46–70 | Arcane Scars: −20% success chance; hostile durations −30%; war damage −20% |
| 71–99 | Ley Tempest: −35% success chance; war damage −40% |
| 100+ | Ley Shatter: Next 3 incoming spells auto-fail regardless of attacker Spell Power. Flux resets to 45, then 6-tick immunity to further Shatter accumulation. |

Ley Shatter is visible to your realm (they can see the protective burst ready) but **not to enemies** — they cannot scout it via Revelation. Creates genuine strategic uncertainty: an attacker probing a high-Flux target risks hitting a Shatter window without knowing it.

Better than Rejuvenation because: triggers on actual damage received (not cast volume), scales continuously and proportionally, the climax moment (Ley Shatter) is satisfying rather than arbitrary, and protects genuinely defenceless targets rather than politically active ones.

### Magical Fortitude (v5)

Replacement for Rejuvenation. Triggers based on measured vulnerability rather than cast traffic:

- A dominion qualifies when it has taken war spell damage in recent ticks **AND** its Ward Power is below a threshold relative to land size
- Protection scales with how low Ward Power is (structural defencelessness), not how many spells landed
- Effect: reduced incoming war spell damage and hostile spell success rates
- Cancelled if the protected dominion invests in Wizard Guilds past the threshold — protection ends when the vulnerability is addressed

Large military attackers who draw retaliation but have normal building investment do not qualify. Only genuinely defenceless dominions (low Ward Power, low guild investment) trigger the protection.

### Brace (magic-redesign.md)

A reactive defensive magic action:
- Available once every 12 hours
- Cost: 20 Wizard Strength
- Effect: For the next 6 hours, reduce incoming hostile spell success by 25% and war spell damage by 20%
- Always applies — no success roll required
- Visible to opponents who cast Revelation (shown as an active effect)

Defenders who read the situation correctly can negate a significant portion of an incoming assault. Attackers want to catch defenders before they Brace. More valuable to defenders (who have spare Wizard Strength from not attacking) than to attackers.

---

## 12. Late-Round Scaling

**Military compounds through elites, tech, and land. Magic needs equivalent late-round growth.**

### Arcane Attunement (leylines, v5)

A permanent accumulating score (similar to Wizard Mastery in structure) that scales magic into the late round.

**Accumulation sources:**
- Wizard Mastery overflow — once Mastery caps, subsequent events contribute to Attunement
- Sustained Wizard Guild investment — small passive gain per tick proportional to guild land percentage
- Successful dispel/cleanse events
- Ritual contributions

**Tier effects:**
- Tier 1 (0–200): Resonance production +5%, spell mana costs −3%
- Tier 2 (201–600): Self-spell durations +2h, Ward Power +5%, Resonance +12%
- Tier 3 (601+): Unlocks enhanced version of School's unique spell (or enhanced Arcane Ward for unaffiliated); self-spell durations +4h; Ward Power +12%; Ley Thread contribution +20%

A player who has maintained Wizard Guilds all round and contributed to many Rituals enters late-round in Tier 3 with dramatically enhanced mana production, longer buffs, and a stronger unique spell.

### Mastery Accumulation Broadened (v3, v4, v5)

Currently Wizard Mastery accumulates only through successful offensive casts, compounding advantage for those already winning.

**Additional accumulation sources:**
- Successful defensive repels — when an incoming hostile spell fails due to Ward Power, the defender gains Mastery at 40% of the offensive rate
- Passive accumulation from Wizard Guilds — small gain per tick based on guild land percentage
- Successful dispels/counterspells
- Ward charges consumed (absorbing enemy magic builds experience)

All playstyles now accumulate Mastery through engagement with the magic system.

### Invasion Momentum (v3)

Attacker's Spell Power increases by +8% for 12 hours after a successful invasion. As the round intensifies and invasions become more frequent, attacker magic power rises in parallel. Magic doesn't fall behind military — it rides the same activity curve.

---

## 13. Spell Sequencing and Combos

**Certain spells amplify each other when cast in sequence. Correct sequencing produces better outcomes than casting spells independently. (magic-redesign.md)**

| Setup Spell | Follow-Up | Bonus | Mechanic |
|---|---|---|---|
| Earthquake | Lightning Bolt | +30% damage | Rubble disrupts magical defenses while Earthquake is active |
| Insect Swarm | Fireball | +20% peasant damage | Suppressed food leaves less population buffer |
| Plague | Fireball | +15% peasant damage | Weakened, growth-suppressed population |
| Amplify Magic | Any war spell | +40% damage | Double mana cost variant; mana cost doubles but war spell is dramatically empowered |

**Combo denial:** Spell Reflect/Arcane Ward can strip an active setup spell before the follow-up lands, breaking the chain. Brace reduces the follow-up damage but does not strip the setup. A defender who successfully denies a combo window has outplayed the attacker; a defender who failed to respond understands why they took extra damage.

Combos are additive bonuses, not requirements. A player who casts independently still plays a valid game. Benefits are visible in spell descriptions.

---

## 14. Rankings Reform

**The current damage leaderboard rewards farming defenceless targets.**

### Arcane Contribution Index / Arcane Impact (leylines, v5)

Replaces raw damage output as the magic ranking.

Per-cast scoring formula:
```
Score = Base Damage × Ward Difficulty Multiplier × Targeting Premium − Diminishing Returns
```

**Ward Difficulty Multiplier** scales with target's Ward Power at cast time:
- Target Ward Power 0–0.5: 0.25× (minimal credit for farming defenceless)
- Target Ward Power 0.5–1.0: 0.75×
- Target Ward Power 1.0–1.5: 1.25×
- Target Ward Power 1.5–2.0: 2.0×
- Target Ward Power 2.0+: 3.0× (maximum credit for hardest targets)

**Targeting Premium:** +25% if target successfully invaded someone in the last 24 hours (targeting an active threat, not a passive one).

**Diminishing Returns:** Each subsequent hit on the same target within 24 hours reduces their Ward Difficulty Multiplier contribution by 25%. Repeatedly hitting the same soft target gives shrinking credit.

**Additional scoring sources:**
- Ritual contribution above minimum threshold
- Successful cleanse events
- Ley Thread contribution per tick (significant over a round)
- Ley Infusion / Bolster cast on depleted realmmates

**Second ranking column — Arcane Resilience:** Tracks Ward Power maintained, successful cleanses, and Ley Flux absorbed without dying. Rewards the defensive and supportive dimensions previously invisible to rankings.

---

## 15. Specific Spell Redesigns

### Disband Spies

**Problem:** A cross-system hard counter that lets a magic-dominant player cascade their ratio advantage into espionage dominance — one hostile spell can undo an entire espionage investment.

**Options:**
- **Reclassify as a war operation** (v3): Success determined by spy ratio, not wizard ratio. Winning the wizard ratio no longer cascades into espionage.
- **Reduce rate and add cooldown** (v4): Conversion rate reduced from ~3% to 1.5%. 24-hour per-target cooldown added. Prevents wiping a spy corps in a single session while preserving the spell's cross-system threat role.
- **Remove entirely** (redesign.md): No cross-system hard counter.

### Racial Spell Redesigns (redesign.md, v2)

**Unholy Ghost (Dark Elf/Spirit):** Reclassified as a School of Ruin effect. Applies a special Draftee Blight (enemy draftees contribute 50% DP instead of 100% during invasions). Non-stackable, 10-hour duration. Preserves power as a sustained-investment mechanic rather than a free passive toggle.

**Erosion (Merfolk/Lizardfolk):** Becomes a targeted, voluntary application rather than automatic terrain conversion. Caster selects which conquered acres to rezone (up to 50% per cast). No more accidental transformation of wanted land types.

**Gaia's Light / Gaia's Shadow (Wood Elf):** No longer mutually penalizing. Gaia's Light grants a Spell Power bonus; Gaia's Shadow grants a spy power bonus. Neither penalizes the other. The choice is which to boost, not which penalty to accept.

**Death and Decay (Undead):** Two explicit cast modes at cast time with separate confirmation prompts. Ruin Mode (Blight Stacks on enemy) requires the same deliberate selection as Conversion Mode (internal food decay + peasant conversion). Conversion Mode cannot be cast accidentally — requires deliberate opt-in.

**Immortal Wizards / Resolute Archmages:** The perk is renamed and redesigned since wizards no longer die on failure (replaced by Arcane Overload) in several redesign variants. New function: Archmages of this race generate +50% Ward Rating per unit.

### Cyclone

Remove the perverse incentive to destroy your own realm's wonders. Flat damage regardless of owned/unowned status. +30% damage during mutual war only.

### Amplify Magic

In magic-redesign.md, gains a second activation mode: if consumed to enhance a **war spell** instead of a self spell, mana cost doubles but war spell deals +40% damage. Chosen at cast time of the follow-up, not at Amplify Magic cast time.

---

## 16. Two-Mana-Pool System (leylines only)

The most radical mana redesign. Replaces a single mana pool with two distinct resources:

**Resonance** — Personal mana. Produced by Towers and Wizard Guilds per tick. Decays at 3%/tick (faster than current). Used for self-spells and info spells. Rewards active casters; punishes passive stockpiling.

**Ley Charge** — Realm mana. Accumulates in a shared Ley Reservoir (one per realm). Every dominion contributes Ley Threads passively each tick based on Wizard Guild land percentage. Used for friendly spells, war spells, and Rituals.

```
Ley Thread contribution per tick = Wizard Guild % × Base Rate × Mastery Modifier
```

The Ley Reservoir has a per-realm tick cap. Any realm member can draw from it to cast war spells and friendly spells. A member who is offline still contributes threads passively. The realm's combined magical capacity exceeds what any individual player could maintain.

---

## Design Tradeoffs Summary

| Dimension | Simple Option | Complex Option |
|---|---|---|
| Offense/defense split | Building Resistance + 0.3× wizard ratio (v3) | Full SP/WR or Offensive Weave/Ward Power split (v5, leylines) |
| Dispel | Always-succeeds at mana cost (v3, v4) | Ward-Power-based with cooldown (v5, leylines) or Stance system (v2) |
| Specialization | No spec system (v3, v4) | Doctrines (magic-redesign.md), Schools (leylines), or Mastery Paths (redesign.md, v2) |
| Teamwork | Universal support spells any member can cast (v3, v4, v5) | Ritual system (leylines) or Arcane Congress (redesign.md, v2) |
| War spells | Suppression replaces destruction (v3) | Bounded damage redesign (v4) or Blight Stack framework (v2, redesign.md) |
| Mana | Remove decay, add Surge Charges (v2, redesign.md) | Two pools: Resonance + Ley Charge (leylines) |
| Protection | Arcane Saturation (v2) or Magical Fortitude (v5) | Ley Flux with Ley Shatter (leylines) |
| Intelligence | Arcane Forecast (magic-redesign.md) | Persistent Wards (v2, redesign.md) |

The v3 document explicitly chose the "minimum viable changes" path — preserving mana decay, wizard ratio formula, and adding only 3 new terms. The leylines document went the furthest in complexity and team integration. The v4/v5 documents sit between these extremes.
