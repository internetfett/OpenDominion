# The Leyline System — A Complete Magic Redesign

---

## Design Philosophy

Magic in OpenDominion should feel like the *realm's* power, not just an individual's stat. The current system treats magic as a parallel solo arms race — wizards vs. wizards, ratio vs. ratio — with friendly spells tacked on as a secondary consideration. This redesign inverts that priority. Teamwork is the primary mechanism. Individual casting is the secondary layer.

Three guiding constraints shaped every decision here:

1. **Players need sleep.** No mechanic should require everyone to be online simultaneously. Cooperative features must have asynchronous contribution windows.
2. **Defense should not require offense investment.** A dedicated military player should be able to build meaningful magical defense without diverting population to wizards.
3. **Magic must stay relevant into the late round.** Mechanics that compound over time — not just tech bonuses — should reward sustained magical investment.

---

## What Changes, and Why

### The Arms Race Problem

The single root cause of most magic issues is that wizard ratio drives both offense and defense from the same resource. Attackers split their population between offense, defense, and wizards. Defenders only need defense and wizards. Defenders will always win this arms race because they have a population surplus. No fix that treats wizard ratio as both the offensive and defensive stat can solve this structurally.

**The fix:** Split into two completely separate stats that feed from different sources.

### The Teamwork Problem

Friendly spells are locked behind two realm roles and are single casts with short durations. The system doesn't give most players a meaningful way to contribute to team magic. When a realmmate is under attack, 10 of 12 realm members have nothing they can do.

**The fix:** A Ritual system with asynchronous contribution windows so anyone can help on their own schedule.

### The Scaling Problem

Military compounds through elites, tech, and land. Magic grows only through tech and wonders — no equivalent to elite unit progression. Magic players fall behind at exactly the moment they should matter most.

**The fix:** A tiered late-round progression system that rewards sustained magical investment through the full round.

---

## Core Statistics

### Offensive Weave

Replaces wizard ratio for offensive purposes only.

```
Offensive Weave = (Wizards + Archmages×2 + Partial Wizard Units) / Total Land
                  × (Race Perk + Spires Bonus + Tech Bonus + Wonder Bonus + Hero Bonus)
```

Governs the success probability of hostile spells, war spells, and info spells when *attacking*. Never influences how hard you are to hit.

### Ward Power

Replaces wizard ratio for defensive purposes only. **Derived entirely from buildings.**

```
Ward Power = (Wizard Guild % × 3.0) + (Tower % × 0.8) + Spires Flat Bonus
             × (Tech Multiplier + Wonder Multiplier)
```

How difficult you are to hit with hostile or war spells is a function of building investment, not unit training. A pure military player who builds Wizard Guilds has meaningful magical defense without training a single wizard.

**Critical consequence:** Explorers who want to be both hard to hit *and* effective casters now need investment in *both* wizard units (Offensive Weave) *and* guild buildings (Ward Power). The arms race is broken because offense and defense are no longer fed by the same resource.

### Spell Success Formula

```
Success Probability = f(Offensive Weave / Target's Ward Power)
```

Same exponential curve shape as before. At equal ratio, success is meaningful but not guaranteed. Caster greatly outmatching target approaches the cap. Target greatly outmatching caster falls steeply.

Failure consequences (wizard losses) scale with (Target Ward Power − Caster Offensive Weave) — probing a heavily warded target remains costly.

---

## Two Mana Pools

Current mana is replaced by two distinct resources with different purposes and rhythms.

### Resonance

**Personal mana.** Produced by Towers and Wizard Guilds per tick. Decays at 3%/tick (faster than current mana). Used for self-spells and info spells — the individual casting layer.

Resonance is volatile by design: it rewards active casters who use it frequently and punishes passive stockpiling even more than current mana. The high decay rate means a dominion that builds Towers but never casts is burning through production for nothing.

### Ley Charge

**Realm mana.** Accumulates in a shared **Ley Reservoir** — one pool per realm. Every dominion contributes Ley Threads to the reservoir passively each tick based on their Wizard Guild land percentage. Used for friendly spells, war spells, and Rituals.

```
Ley Thread contribution per tick = Wizard Guild % × Base Rate × Mastery Modifier
```

The Ley Reservoir has a per-realm tick cap to prevent unlimited accumulation. Ley Charge in the reservoir decays slowly (1%/tick). Any realm member can draw from the reservoir to cast war spells and friendly spells, with draw access governed by wizard strength (spending strength "opens" your channel to the ley network).

**Why this matters:** Realms with active magic players collectively build a war chest. A member who is asleep still contributes threads to the pool. The realm's combined magical capacity exceeds what any individual player could maintain. This is the structural foundation of team magic.

---

## Ley Flux: The Battle-Hardening Meter

Replaces Rejuvenation entirely.

**Ley Flux** is a per-dominion score that measures how much magical punishment a dominion has absorbed recently. It rises when you are hit and decays naturally over time — you gradually shed battle scars during peace.

### Accumulation

| Event | Flux Gained |
|---|---|
| Hostile spell lands (any type) | +8 |
| War spell lands and deals damage | +18 |
| Cleanse successfully removes a debuff | −5 (relief) |

### Natural Decay

Flux decays at **4 per tick** passively. If no spells have landed recently, Flux reaches 0 in about a day.

### Flux Thresholds

| Flux Range | Effect |
|---|---|
| 0–20 | No effect. Normal state. |
| 21–45 | **Battle-Wary.** Incoming hostile spell success chance −8%. Your Ley Threads to the realm pool increase 15% (stress drives magic). |
| 46–70 | **Arcane Scars.** Success chance −20%. Hostile spell durations reduced by 30%. War spell damage reduced 20%. |
| 71–99 | **Ley Tempest.** Success chance −35%. War spell damage reduced 40%. Enemies hitting you receive half the normal Arcane Contribution Index credit. |
| 100+ | **Ley Shatter.** The accumulated magical abuse triggers a cascading ley resonance. The *next 3 incoming hostile or war spell casts automatically fail*, regardless of caster Offensive Weave. After all 3 reflect, Flux resets to 45 and a 6-tick immunity to further Ley Shatter accumulation begins. |

**Ley Shatter** is visible to your realm on their dashboard (they can see you have a protective burst ready or burning). It is **not visible to enemies** — they cannot scout it through Revelation or any info spell. This creates genuine strategic uncertainty: an attacker probing a high-Flux target risks hitting a Shatter window without knowing it.

### Why This Works Better Than Rejuvenation

Rejuvenation triggered based on incoming cast *volume*, which correlated with being politically significant — the big military attacker drawing retaliation got blanketed in spell immunity. Ley Flux protects based on actual damage received, scales continuously, is proportional to actual punishment taken, and the climax moment (Ley Shatter) is satisfying rather than arbitrary. The attacker does not know when Shatter is ready; the defender does.

---

## Arcane Depletion (Reworked Snare)

Renamed from "snare" to reflect what is actually happening: the wizard's internal ley connection is depleted, not locked out entirely.

### What Changes

When Wizard Strength falls below the casting minimum:

- **Hostile spells, war spells, and info spells** are blocked (unchanged — you cannot attack)
- **Self-spells are still castable**, but cost 2× Resonance and each cast costs an additional 5 Wizard Strength (drawn from whatever remains, can push to 0 but no lower)
- **Ley Threads continue flowing** to the realm reservoir — you are still contributing to the team even while depleted
- **Resonance-to-Strength emergency conversion:** You can spend 80 Resonance to recover 5 Wizard Strength, up to 3 times per depletion event. This is a last resort — expensive, limited, but it exists.

### Ally Support

Any realm member can cast **Ley Infusion** (see Friendly Spells) on a depleted realmmate. This transfers 15% of the caster's current Wizard Strength to the target. Caster cannot cast this if they themselves are below 40% strength.

The Resilience mechanic (accelerated recovery while depleted) is retained and made **explicitly visible in the UI** — the tooltip on the strength bar shows the current accelerated recovery rate while depleted, so the mechanic is legible, not hidden.

---

## Arcane Schools: Magical Identity

Each dominion optionally selects an **Arcane School** at the start of the round. Schools are independent of race — any race can choose any school. The default (no school, "Unaffiliated") gives access to all basic generic spells at normal cost.

Schools provide a specialist identity for magic-focused players without punishing those who prefer to stay general.

### School of Evocation

*Mastery of raw magical force.*

- **Passive:** War spell damage +15%. Resonance cost of war spells +10%.
- **Unique spell: Eldritch Storm** — War spell. Deals damage to a specific land type on the target's terrain (reduces the effectiveness of that land type's unit scaling perks for 6h). Does not deal peasant or building damage.
- **Ritual bonus:** Evocation contributions to Rituals weighted +30% for offensive ritual types.
- **Design role:** Realm's primary magical attacker. Best when coordinated with military players to time invasions alongside Eldritch Storm windows.

### School of Abjuration

*Mastery of warding and protection.*

- **Passive:** Ward Power per Wizard Guild increased by 50%. Incoming war spell damage −10%.
- **Unique spell: Dimensional Anchor** — Hostile spell. Prevents the target from recasting one specific self-spell for 12 hours (the caster chooses which spell to anchor at cast time). Does not remove an active spell — blocks recasting only.
- **Ritual bonus:** Abjuration contributions to defensive Rituals weighted +40%.
- **Design role:** Realm's magical shield. Exceptionally hard to successfully cast against. The Dimensional Anchor creates targeted disruption against magic-dependent races.

### School of Conjuration

*Mastery of resource and entity summoning.*

- **Passive:** Ley Thread contribution to the realm reservoir doubled. Resonance generation +10%.
- **Unique spell: Mana Siphon** — Hostile spell. Drains a flat amount of Resonance from the target's personal pool. The drained amount is added to the realm Ley Reservoir (not to the caster directly — this is a team resource, not personal theft). Duration: instant.
- **Unique spell: Thrall Weave** — Self spell. Converts Ley Charge directly into defensive draftees over 12 ticks (rate scales with Conjuration progress). A mana-to-military conversion valve.
- **Design role:** Realm's magical engine. Supercharges the Ley Reservoir for Ritual use. In a realm with a Conjurer, Rituals happen faster and more powerfully.

### School of Divination

*Mastery of sight and subtle manipulation.*

- **Passive:** Info spells never fail. Hostile spell durations +25%, hostile spell Resonance cost −15%.
- **Unique spell: Weave Reading** — Info spell. Reveals the target's current Ley Flux level, active Rituals being run by their realm, and whether any realm members are currently Arcane Depleted. Far more intelligence than Clear Sight.
- **Unique spell: Entropy Hex** — Hostile spell. Does not directly damage the target. Instead, it suppresses one of the target's active self-spell bonuses to 50% effectiveness for 8 hours (the specific spell targeted is visible to the caster on cast, chosen from the target's active list). Not a duration reduction — the buff stays on but at half power.
- **Design role:** Intelligence and disruption specialist. Pairs with attackers who need to know exactly when to strike. Entropy Hex is uniquely powerful against magic-dependent races relying on racial self-spells for combat bonuses.

---

## The Ritual System: Asynchronous Teamwork

Rituals are the centerpiece of this redesign. They are cooperative casting events where multiple realm members contribute to a single powerful effect over an extended window.

### How Rituals Work

1. **Initiation:** A realm member with the Magister, Grand Magister, or Monarch role initiates a Ritual from the Magic page. They choose the Ritual type, optionally choose a target (for offensive Rituals), and commit an initial Ley Charge from the realm reservoir. The Ritual becomes visible to all realm members.

2. **Contribution Window:** For the next **8 hours**, any realm member can contribute Ley Charge or Resonance to the Ritual. Contributions are logged by member. The Ritual's power at resolution scales with total contributions received.

3. **Resolution:** At the end of the 8-hour window (or when manually triggered by the initiator at any point after 4 hours), the Ritual fires. Its effect is applied with power determined by total contribution.

4. **Sleep-Friendly Design:** The 8-hour window means a Ritual started at 10pm local time resolves at 6am — capturing contributions from morning players. A Ritual started at 8am captures contributions from players across multiple waking sessions. No one needs to be online at the moment it fires.

### Ritual Power Tiers

Total contributions determine the Ritual's tier at resolution:

| Tier | Required Contribution | Effect Multiplier |
|---|---|---|
| Flicker | Minimum threshold met | 0.5× base effect |
| Stable | Moderate contribution | 1.0× base effect |
| Resonant | Strong contribution | 1.5× base effect |
| Ascendant | Maximum (very hard to achieve) | 2.5× base effect |

### Ritual Catalog

**Veil of Ashes** — *Defensive*
- Effect: Reduces all incoming hostile spell success rates against every realm member by a flat percentage for 12 hours.
- Tier scaling: Flicker: −10%. Stable: −20%. Resonant: −30%. Ascendant: −45%.
- Design: The realm's primary coordinated defense. A well-timed Veil before an expected magical assault blunts the entire offensive.

**Arcane Reckoning** — *Offensive*
- Effect: Simultaneously strikes the 3 most recently active dominions in a chosen enemy realm with Plague + Insect Swarm, applied as if from a wizard with the initiating realm's average Offensive Weave.
- Tier scaling: Higher tiers extend duration and increase the success chance override.
- Restriction: Requires active war with the target realm.
- Design: A punishing coordinated magical offensive. Requires significant realm investment.

**Ley Surge** — *Economic*
- Effect: Increases every realm member's Resonance production from Towers by 25% for 24 hours.
- Tier scaling: Higher tiers extend duration.
- Design: Allows magic-heavy realms to sustain casting campaigns. Lower priority but tactically valuable before high-action windows.

**Shattered Aegis** — *Offensive*
- Effect: Temporarily reduces the target dominion's effective Ward Power by 30% for 6 hours. All friendly and hostile spell effects during this window ignore the suppressed Ward Power portion.
- Tier scaling: Higher tiers increase the suppression percentage and duration.
- Restriction: Cannot target a dominion with active Ley Flux above 60 (you cannot Shatter someone already in Ley Tempest — this prevents abuse chaining).
- Design: The attack-window opener. A coordinated realm uses Shattered Aegis to create a brief vulnerability window, then floods the target with hostile spells before it expires. Requires planning and communication. Punishes sleeping on defense.

**Convergence Ward** — *Defensive*
- Effect: Applies a shielding effect to a single chosen realmmate. The next incoming war spell against that target is fully absorbed and negated. After the absorption, the ward expires.
- Tier scaling: Higher tiers add more absorb charges (Stable: 1 absorb, Resonant: 2, Ascendant: 3).
- Design: Targeted protection for a specific realmmate under concentrated assault. More precise than Veil of Ashes. Often used to protect a key military player who has been targeted.

**Ley Beacon** — *Utility*
- Effect: For the next 24 hours, the initiating realm can see the Ley Flux level of every dominion in a chosen enemy realm (visible on the War Ops page). Also reveals any active Rituals currently running in the enemy realm.
- Tier scaling: Higher tiers extend the intelligence window.
- Design: Pure intelligence. Reveals defensive posture and coordinated threats. Pairs naturally with Divination players.

### Ritual Visibility

- Active Rituals in your realm are visible to all realm members — you can see what's running and contribute.
- **Enemy Rituals are not visible** unless a Ley Beacon Ritual or Divination Weave Reading reveals them.
- Completed Rituals leave a visible log entry for the realm.

### Reward for Participation

Individual realm members who contribute above a minimum threshold to a successful Ritual receive:
- Wizard Mastery (small amount, scales with contribution)
- Arcane Contribution Index credit (see Rankings)
- A "Ritual Veteran" counter tracked on their dominion — higher counts eventually unlock access to a Prestige-like passive bonus to Ley Thread contribution rate.

---

## Arcane Attunement: Late-Round Scaling

A new permanent accumulating score that addresses magic falling behind military in late-round relevance.

### Accumulation Sources

| Source | Rate |
|---|---|
| Each tick with Wizard Guilds above 5% land | Small flat gain per tick |
| Wizard Mastery overflow (past cap) | Converts to Attunement |
| Successful Ritual contribution | Moderate gain |
| Successful Cleanse (see below) | Small gain |

### Attunement Effects by Tier

**Tier 1 (Early — 0-200 Attunement)**
- Resonance production from Towers +5%
- Spell mana costs −3%

**Tier 2 (Mid — 201-600 Attunement)**
- All self-spell durations +2 hours
- Ward Power passive bonus: flat +5% of current Ward Power
- Resonance production from Towers +12%

**Tier 3 (Late — 601+ Attunement)**
- Unlocks **Resonant Spells** — one enhanced version of your School's unique spell, with increased power and reduced cost. If Unaffiliated (no school), unlocks an enhanced Arcane Ward.
- Self-spell durations +4 hours total
- Ward Power passive bonus +12%
- Ley Thread contribution rate to realm reservoir +20%

A Conjuration player who has maintained Wizard Guilds all round and contributed to many Rituals enters the late-round in Tier 3 with dramatically enhanced mana production, longer buffs, and a stronger unique spell. Military power compounds through elites; magic power now compounds through Attunement.

---

## Friendly Spells: Open Access

The role restriction on friendly spells is abolished for basic protection.

### New Access Rules

| Spell | Who Can Cast | Strength Modifier |
|---|---|---|
| Arcane Ward | Any realm member | Ward Power of caster scales effect |
| Illumination | Any realm member | Ward Power of caster scales effect |
| Ley Infusion | Any realm member | Transfers wizard strength to depleted realmmate |
| Convergence Ward | Requires Ritual (any member can contribute) | Ritual-powered |
| Arcane Bulwark | Grand Magister or Court Mage only | Full effect |

**Arcane Bulwark** replaces Spell Reflect and remains role-restricted because it is the most powerful single-target defensive spell:
- Grants a **percentage resistance chance** against each incoming hostile or war spell (probability, not single-use block)
- Has a charge pool: each successful deflection consumes a charge; when all charges are depleted, it expires
- **Not visible to Revelation** — enemies cannot scout it
- Duration: 8 hours base. Recast before expiry replenishes charges without resetting the timer
- Grand Magister version: +2 base charges, duration +2 hours

**Ley Infusion** (new):
- Any realm member can cast on a depleted (below casting threshold) realmmate
- Costs 15% of caster's current Wizard Strength
- Target receives 10% Wizard Strength (the 5% inefficiency is the cost of transferral)
- Cannot be cast by anyone below 40% of their own strength
- Cooldown: 4 ticks between casts on the same target

This makes snare a social event. Your realmmates can help you. It costs them something real. It rewards a realm that coordinates and communicates — and the 4-tick cooldown prevents fully negating depletion through repeated infusion.

---

## Cleanse: Active Counterplay Against Debuffs

**Cleanse** is a new reactive self-cast that removes one active hostile debuff.

### Mechanics

- **Cast type:** Reactive (can be cast from the current debuff list, targeting a specific active hostile spell)
- **Mana cost:** Moderate Resonance cost (similar to an info spell)
- **Success roll:** Based on (Ward Power / Debuff Potency). Each hostile spell type has a fixed potency value. A target with high Ward Power cleanses reliably; a target with low Ward Power has meaningful but imperfect success.
- **Cooldown:** 6 ticks after cleansing a given spell type before that same spell type can be cleansed again. (You cannot trivially null-ify all hostile pressure — each cleanse clears one debuff then pauses.)
- **Cannot cleanse while Arcane Depleted.**

### Debuff Potency Table

| Spell | Potency |
|---|---|
| Plague | 2.0 |
| Insect Swarm | 1.8 |
| Earthquake | 1.6 |
| Great Flood | 1.6 |
| Dimensional Anchor | 2.2 |
| Entropy Hex | 2.4 |
| Disband Spies | Not cleansable (instant effect, nothing to remove) |

Cleanse success on a potency 2.0 spell at Ward Power 1.0 (equal) is approximately 50%. At Ward Power 2.0 against potency 2.0, success approaches the cap. At Ward Power 0.5, it falls steeply. Building Wizard Guilds directly improves your ability to fight back once debuffed.

---

## Hostile Spell Revisions

### Impact Increased

Hostile spells that were too marginal to bother with are meaningfully stronger now that they can be actively fought via Cleanse. Plague, Insect Swarm, Earthquake, and Great Flood each increase in base effect. Specific tuning numbers belong in a balance pass, but the directional intent is: debuffs should hurt enough that removing them is worth the effort, and landing them is worth the investment.

### New Hostile Spells

**Mana Siphon** (Conjuration School only) — See School of Conjuration above.

**Entropy Hex** (Divination School only) — See School of Divination above.

**Dimensional Anchor** (Abjuration School only) — See School of Abjuration above.

### Removed

Rejuvenation is removed entirely. Replaced by Ley Flux.

---

## War Spell Revisions

### Fireball

Unchanged. Wizard Guilds protect a number of peasants scaling with guild count.

### Lightning Bolt

**Now has equivalent building-based protection.** Each percentage point of land occupied by Masonry reduces Lightning Bolt castle improvement vulnerability at the same marginal rate that each Wizard Guild reduces Fireball peasant vulnerability. Maximum protection 50%.

Additionally, the new **Arcane Hardening** self-spell provides flat Lightning Bolt damage reduction, stacks with Masonry up to the existing 80% damage reduction cap.

Lightning Bolt is no longer the outlier war spell with no non-ratio-based counterplay.

### Burning and Lightning Storm Status Effects

Status effects now apply a **flat damage multiplier** to subsequent same-type war spells, not a compounding one. A second cast while the effect is active extends the duration by a partial amount rather than stacking additional damage. The intent shifts from "spam to compound" to "apply and maintain." The snowball dynamic that made an opponent feel helpless is replaced by a sustained pressure model.

### Eldritch Storm (Evocation School only)

See School of Evocation above.

---

## New Self Spells

| Spell | Duration | Effect |
|---|---|---|
| Arcane Hardening | 12h | Reduces incoming Lightning Bolt damage by flat 15%; stacks with Masonry up to 80% cap |
| Emergency Resonance | Instant | Cooldown spell. While Arcane Depleted, convert 80 Resonance → 5 Wizard Strength; max 3 uses per depletion event |

---

## Rankings: Arcane Contribution Index

Current spell damage rankings incentivize farming defenceless targets. The **Arcane Contribution Index (ACI)** replaces raw damage as the magic leaderboard.

### Per-Cast Score Formula

```
ACI Gain = (War Spell Damage OR Hostile Spell Value)
           × Ward Difficulty Multiplier
           × Targeting Premium
           − Diminishing Returns Penalty
```

**Ward Difficulty Multiplier** — Scales with target's Ward Power at time of cast:
- Target Ward Power 0–0.5: 0.25× (very low credit for farming defenceless)
- Target Ward Power 0.5–1.0: 0.75×
- Target Ward Power 1.0–1.5: 1.25×
- Target Ward Power 1.5–2.0: 2.0×
- Target Ward Power 2.0+: 3.0× (maximum credit for hitting the hardest targets)

**Targeting Premium** — +25% if the target successfully invaded someone in the last 24 hours (targeting an active threat, not a passive target).

**Diminishing Returns** — Each subsequent hit on the same target within 24 hours reduces their Ward Difficulty Multiplier contribution by 25%. Repeatedly hitting the same person gives rapidly shrinking credit.

### Additional ACI Sources

| Activity | ACI Gain |
|---|---|
| Ritual contribution above minimum threshold | Moderate (scales with tier achieved) |
| Successful Cleanse | Small |
| Ley Thread contributed to reservoir per tick | Tiny per tick, significant over the round |
| Ley Infusion cast on a depleted realmmate | Small |

A dedicated Conjuration player who never casts a single hostile spell but consistently powers the Ley Reservoir and contributes to Rituals will appear meaningfully on the ACI leaderboard. Magic contribution is not synonymous with aggression.

**Second ACI Column: Arcane Resilience** — Tracks Ward Power maintained over the round, successful Cleanse events, and Ley Flux absorbed without dying. Visible on the rankings page alongside ACI. Rewards the defensive and supportive dimensions of the magic system that were previously invisible.

---

## Building Roles Summary

With this redesign, every magic-adjacent building has a distinct, non-redundant purpose:

| Building | Primary Purpose | Secondary Purpose |
|---|---|---|
| Tower | Resonance production (personal mana) | Minor Ward Power contribution; Ley Thread contribution |
| Wizard Guild | Ley Thread contribution (realm mana) | Major Ward Power contribution; Fireball peasant protection |
| Shrine (Hill) | Hero XP amplification | — |
| Masonry | Lightning Bolt protection | Castle improvement efficiency |

The previous Tower vs. Wizard Guild decision was primarily about mana volume vs. peasant protection. Now it is Tower (personal casting power and mana) vs. Wizard Guild (team mana, team defense, and your own Ward Power). Both matter for different reasons. Neither is universally dominant.

---

## Player Decision Space

### Individual Magic Decisions

**School selection** — Chosen at round start, permanent. Commits the player to a specialist identity. Evocation players plan to be the realm's magical hammer; Abjuration players accept a defensive role; Conjuration players are infrastructure; Divination players provide intelligence and disruption. Unaffiliated players retain full generalist access at no bonus. School choice is a conversation piece during pack formation — a realm that coordinates its school distribution has real advantages.

**Offensive Weave vs. Ward Power** — Training wizards raises Offensive Weave. Building Wizard Guilds raises Ward Power. These no longer trade against each other through the same resource. A glass-cannon caster (high Offensive Weave, few guilds, low Ward Power) is easy to hit back. A pure ward-builder (high Ward Power, few wizards) is hard to hit but cannot launch effective offensive casts. Balance is optimal but expensive in land.

**Resonance discipline** — 3%/tick decay is punishing. A player who builds Towers and doesn't cast constantly wastes production. Cast schedules should match Resonance production. Consider School bonuses before building tower-heavy layouts.

**Arcane Depletion management** — Emergency Resonance conversion exists but is expensive. Coordinate with realmmates for Ley Infusion coverage during high-tempo windows. Don't let wizard strength hit zero before an expected assault — the same morale/tempo awareness that applies to military applies to magic.

**Cleanse timing** — Hostile debuffs now hurt enough to be worth fighting. The 6-tick cooldown per debuff type means you can't cleanse everything simultaneously. Prioritize which debuffs to cleanse based on your current strategic situation. A food-limited dominion should immediately cleanse Insect Swarm. A building-critical dominion prioritizes Earthquake. Divination enemies may cast Entropy Hex on your key racial spell — cleanse that first.

### Realm Magic Decisions

**Ritual initiation timing** — Who initiates, when, and which type requires reading the strategic situation. A Shattered Aegis before a coordinated military assault needs to align with the attack window. A Veil of Ashes should be run during periods of expected incoming magical assault. The 8-hour contribution window means a Monday-morning player can start a Ritual that night-shift players fuel.

**School balance in realm composition** — A realm with three Conjuration players has a massive Ley Reservoir but limited offensive output. A realm of all Evocation players has magical firepower but no infrastructure. Diverse school composition improves Ritual output across all types. This is a discussion to have during pack formation.

**Magister role allocation** — The Magister/Grand Magister roles now have two key powers: initiating Rituals and casting the restricted Arcane Bulwark. Placing active, magic-invested players in these roles is more important than before. A sleeping Grand Magister doesn't just lose Arcane Ward access — it blocks Bulwark and delays Ritual initiation.

**Ley Reservoir stewardship** — The reservoir is a shared resource. Spending it on three simultaneous Rituals depletes the team's mana. Coordinating through the Council on which Ritual to prioritize (and when) is a meaningful strategic conversation. Conjuration players who see the reservoir low might shift to passive contribution mode before the next Ritual.

---

## Interactions With Other Systems

**Races & Units** — Races with `counts_as_wizard` unit perks continue to contribute to Offensive Weave. Immortal wizard perks prevent losses on failed offensive casts. Ward Power is building-based, so races with construction cost reduction perks gain a modest magical defense advantage — build more Wizard Guilds for less platinum. Racial self-spells remain unchanged; the School system adds *on top of* racial identity.

**Land & Construction** — Wizard Guilds become the dominant magical defense investment, fulfilling three roles: Ley Thread generation, Ward Power, and Fireball peasant protection. Towers fulfill two: personal Resonance and secondary Ley Thread contribution. The Swamp land triad (Towers, Wizard Guilds, Temples) now has clearer differentiation — Guilds for defense and team mana, Towers for personal mana and slight Ward Power, Temples for enemy DP reduction.

**Military** — The core improvement: military-focused players can have meaningful Ward Power through building investment without sacrificing army composition. Self-buff spells that boost offense/defense continue to multiply into military calculations. Shattered Aegis opens attack windows for coordinated military-magical assaults. Eldritch Storm suppresses terrain-based unit perks — directly interacting with military strategy.

**Espionage** — Illumination (now castable by any realm member) broadens spy op protection. Mana Siphon creates a new cross-system disruption option. Divination's Weave Reading can reveal whether enemy realm members are Arcane Depleted — information useful for timing spy ops.

**Heroes** — Hero perks that currently boost wizard power should be split into Spell Power (Offensive Weave) and Ward Power variants in a balance pass. The Sorcerer class now provides Offensive Weave bonus. A new hero class, **Ward Mage**, provides Ward Power bonus per level — explicitly rewarding the defensive magic identity. Enchantment upgrade reduces wizard strength cost for self-spells while Arcane Depleted (synergizes with the new depletion-doesn't-lock-out-self-spells rule).

**Technology** — New tech perks should include: Ward Power per guild percentage, Cleanse success rate bonus, Ley Thread contribution rate increase, Arcane Bulwark charge count increase, Ritual contribution efficiency (your Resonance contributions count for more). These give technology investment clear magical defense relevance.

**Wonders** — Great Oracle's spell cost reduction applies to Resonance costs only (not Ley Charge). Ivory Tower and Wizard Academy contribute to Ward Power as well as their existing effects. The Astral Panopticon (existing) should also reveal enemy active Rituals, not just spells. A new wonder should exist for the magical team layer — something like the **Ley Nexus**, which increases the realm Ley Reservoir cap and the Ley Thread contribution rate for all realm members.

---

## Summary: Issues Addressed

| Issue | How This Design Addresses It |
|---|---|
| Ratio arms race unwinnable for attackers | Ward Power from buildings is fully independent of wizard unit investment |
| Lightning Bolt has no counterplay | Masonry protection elevated to parity with Wizard Guild/Fireball; Arcane Hardening self-buff adds further ratio-independent counterplay |
| Hostile debuffs are non-interactive | Cleanse provides Ward-Power-based dispel with per-type cooldown; increased debuff impact makes fighting them worthwhile |
| Friendly spells locked behind two roles | Arcane Ward and Illumination open to all realm members; Ley Infusion open to all; Rituals open to all |
| Spell Reflect trivially scouted and probed | Replaced by Arcane Bulwark: invisible to Revelation, multi-charge, probabilistic |
| Snare offers zero agency | Self-spells still castable while depleted; Emergency Resonance recovery valve; Ley Infusion ally support |
| Magic falls off late round | Arcane Attunement compounding through Tiers 1–3; Resonant Spells unlock in late-round for committed players |
| Realm magic strength is luck | Ward Power from buildings is a choice any realm member can make; Ritual system rewards coordinated contribution regardless of wizard race composition |
| Rejuvenation protects the wrong players | Ley Flux triggers on actual damage received and scales continuously; Ley Shatter as satisfying climax; not visible to enemies |
| Rankings incentivize farming defenceless | ACI weights hits by target Ward Power and targeting premium; Ritual/Infusion/defensive contributions score on the same leaderboard |
| No teamwork without roles | Ritual system is explicitly multi-player and asynchronous; any member can contribute during their waking hours |
| Players need sleep | 8-hour Ritual contribution windows span timezones; passive Ley Thread generation continues while offline |
