# Magic System Redesign — Version 5

---

## Design Goals

This redesign addresses ten structural problems identified in the current system:

1. Wizard ratio serves dual offensive and defensive purposes, creating an arms race that non-wizard players can never win
2. War spell counterplay is asymmetric — Fireball has building-based peasant protection; Lightning Bolt has none
3. Hostile spell debuffs are non-interactive — no dispel, no response, no counterplay once they land
4. Friendly spells are locked behind two realm roles, creating single points of failure
5. Spell Reflect is trivially scouted and consumed by probe casts
6. Being snared offers no agency and resilience is unintuitive
7. Magic scales poorly into the late round compared to military
8. Realm magic strength is determined by player distribution luck, not by decisions
9. Rejuvenation applies based on cast traffic, protecting dominant military players who draw retaliation rather than genuinely defenceless ones
10. Damage rankings incentivize farming the defenceless instead of threatening the dangerous

---

## Core Structural Changes

### 1. Split Wizard Ratio Into Spell Power and Ward Power

The single most important change. Currently wizard ratio drives both offense and defense. This is replaced by two separate stats:

**Spell Power** — The offensive casting stat. Determines the probability that hostile, war, and info spells succeed. Calculated from:
- Wizards + Archmages×2 + partial wizard units, relative to total land (the existing wizard ratio formula)
- Modified by race perks, Spires castle improvement, technology, wonders, hero perks
- Amplified by Wizard Mastery

**Ward Power** — The defensive resistance stat. Determines how difficult a dominion is to hit with hostile and war spells. Calculated from:
- Wizard Guild land percentage (primary driver)
- Tower land percentage (secondary, smaller contribution)
- Spires castle improvement
- Technology and wonder bonuses

**The critical design shift:** Ward Power scales with *buildings*, not *units*. An attacker does not need to train a single wizard to have meaningful magical defence. They build Wizard Guilds. Explorers who want to be both hard to hit and effective offensive casters need both a high wizard ratio *and* building investment — a real tradeoff.

The success formula for offensive spells becomes an exponential function of (Spell Power / Ward Power) rather than (caster wizard ratio / target wizard ratio). The curve shape is unchanged; only what feeds into it changes.

Failure consequences (wizard losses when a cast fails) continue to scale with the gap between Spell Power and Ward Power — probing a target whose Ward Power far exceeds your Spell Power remains costly.

---

### 2. Arcane Mastery Accumulates Defensively Too

Currently Wizard Mastery accumulates only through successful offensive casts, compounding advantage for players who are already winning.

Mastery now accumulates through three channels:
- **Successful offensive casts** — unchanged
- **Successful ward events** — when a hostile or war spell aimed at you fails because of your Ward Power, you gain a small mastery contribution. This rewards building investment, not just casting advantage.
- **Passive accumulation from Wizard Guilds** — a very small mastery gain per tick based on current guild land percentage. This gives non-aggressive magic players a floor of mastery growth even during quiet periods.

Mastery bonuses (mana cost reduction, wizard strength recovery) are unchanged.

---

## Wizard Strength

No structural change. Wizard strength remains the casting stamina pool, consumed by each cast and recovered each tick. Rates and thresholds are unchanged.

**Snare Agency Addition:** When snared (strength below casting threshold):
- Resilience and its recovery bonus are now displayed explicitly in the UI — the secret acceleration is no longer hidden
- A new friendly spell, **Channel Mana**, allows realmmates to contribute to recovery (see Friendly Spells)
- `Emergency Ward` — a new self-cast that functions while snared. Uses mana stockpile to restore a fixed amount of wizard strength, bypassing the snare threshold. Has a long cooldown, cannot be cast again until full recovery has occurred. Mana cost is high. This is a last resort, not a routine tool.

---

## Spell Categories

Categories are unchanged: Self, Hostile, War, Info, Friendly. The differences are in the rules governing each.

### Self Spells

No structural changes. The non-stacking rule, Amplify Magic combo, and cooldown mechanics remain.

**New spell: Emergency Ward** — Self only. Works while snared. Spends a significant mana reserve to restore a fixed amount of wizard strength. Cooldown prevents repeated use.

**New spell: Arcane Hardening** — Self only. A moderate-duration buff that reduces incoming Lightning Bolt damage by a flat percentage (independent of Masonry investment — the two stack additively up to the existing hard cap). Gives Lightning Bolt the same ratio-independent counterplay layer that Fireball has through Wizard Guilds.

### Hostile Spells

**Dispel (Cleanse)** is added as a self-cast hostile counter:
- **Cleanse** — Self only, but placed in a new "Reactive" spell category. Removes one active hostile debuff from your own dominion. Success is based on Ward Power vs. the potency of the debuff (debuff potency is a fixed value per spell type). If Cleanse fails, the debuff is not removed and wizard strength is still spent. A target with high Ward Power cleanses reliably; a target with low Ward Power has meaningful but not guaranteed success.
- Cleanse cannot be used while snared.
- Cooldown: cannot Cleanse the same spell type twice within one duration window. This prevents trivially nullifying all hostile pressure — each Cleanse clears one debuff and then pauses.

Hostile spell impact is increased moderately across the board to compensate for the new interactivity. Debuffs that were too marginal to bother with become more meaningful when they can now be fought.

**Timing restrictions:** Unchanged. Hostile spells require being 3 days into the round.

**War bonuses to duration:** Unchanged.

### War Spells

**Fireball:** Unchanged. Wizard Guilds protect a number of peasants scaling with guild count; only the surplus is at risk.

**Lightning Bolt:** Now has equivalent building-based protection. Each percentage point of land occupied by Masonry reduces Lightning Bolt vulnerability at the same marginal rate that each Wizard Guild building reduces Fireball vulnerability. The existing maximum protection cap (50%) remains. The new `Arcane Hardening` self-buff also stacks additively with Masonry up to the hard damage reduction cap.

Lightning Bolt's damage reduction floor (cannot be reduced below 20% of base) is retained.

**Burning and Lightning Storm status effects:** The escalating damage behavior is capped.

- Each status effect now provides a **flat** damage multiplier to subsequent same-type war spells, not a compounding one. The multiplier does not increase with additional casts while the effect is active.
- A second application of the same war spell before the effect expires **does not stack additional damage**. It extends the duration of the active effect by a partial amount.
- The strategic incentive shifts from "spam to compound" to "apply and sustain."

**Rejuvenation** is removed and replaced by **Magical Fortitude** (see below).

### Info Spells

Unchanged. Clear Sight, Vision, Revelation, Disclosure function as now.

**One addition:** **Arcane Bulwark** (see Friendly Spells) is not detectable by Revelation. This is by design — see section on Arcane Bulwark.

### Friendly Spells

**Casting restrictions are opened up.** Any realm member can now cast basic friendly spells on realmmates. Grand Magister and Court Mage retain meaningful superiority, but no longer hold exclusive access.

| Caster | Arcane Ward / Illumination | Arcane Bulwark |
|---|---|---|
| Any realm member | Castable. Effect strength scales with caster's Ward Power | Not available |
| Court Mage | Castable at 1.4× effect strength, 0.8× mana cost | Castable |
| Grand Magister | Castable at 1.6× effect strength, 0.75× mana cost | Castable, enhanced duration |

This eliminates the single-point-of-failure problem: if the Grand Magister goes inactive, realmmates still have access to basic defensive coverage. Roles remain desirable — both for casting quality and for Arcane Bulwark access — but their absence no longer collapses the realm's defensive layer.

**Arcane Bulwark** replaces Spell Reflect:

- Friendly-only, restricted to Grand Magister and Court Mage
- Provides a percentage chance to resist each incoming hostile or war spell for its duration (probability, not single-use block)
- **Not visible to Revelation** — the attacker cannot scout whether the Bulwark is active
- Has a charge pool: each time it successfully deflects a spell, it loses a charge. When all charges are depleted, it expires
- Duration is longer than Spell Reflect (base: 8 hours) but charge count limits its maximum utility against sustained attack
- A new cast before expiry replenishes charges without resetting the timer

This changes the dynamic: Bulwark cannot be probed out with a cheap cast because it has multiple charges, and it cannot be scouted and waited out because it is invisible.

**Channel Mana** — new friendly spell:

- Castable by any realm member on a snared realmmate
- Caster spends a portion of their own wizard strength (and mana) to restore strength on the target
- Transfer is partial: caster loses more than the target receives (there is an inefficiency cost)
- Cannot be used if the caster themselves is near the snare threshold
- Makes snare a more social mechanic — your realm can soften the lockout at real cost to themselves

---

## Spell Listing

### New and Changed Spells

| Spell | Type | Duration/Effect |
|---|---|---|
| Arcane Hardening | Self | 12h — flat Lightning Bolt damage reduction, stacks with Masonry up to hard cap |
| Emergency Ward | Self (reactive) | Instant — restores fixed wizard strength while snared; long cooldown |
| Cleanse | Reactive | Instant — removes one hostile debuff; success based on Ward Power vs. debuff potency; cooldown per debuff type |
| Arcane Bulwark | Friendly (role-restricted) | 8h — hidden percentage resist chance with charge pool; replaces Spell Reflect |
| Channel Mana | Friendly (any member) | Instant — partial wizard strength transfer to snared realmmate |

### Removed Spells

| Spell | Reason |
|---|---|
| Spell Reflect | Replaced by Arcane Bulwark |

### Unchanged Spells

All existing self spells, hostile spells, info spells, and the war spells (with the mechanical changes to Lightning Bolt protection and Burning/Lightning Storm capping noted above) remain. Arcane Ward and Illumination remain but are now available to all realm members at reduced power.

---

## Magical Fortitude (Replacing Rejuvenation)

Rejuvenation's core problem is that it triggers based on incoming cast volume, which correlates with being politically significant (drawing retaliation), not with being defenceless.

**Magical Fortitude** triggers based on measured vulnerability:

A dominion qualifies for Magical Fortitude protection when:

1. It has taken war spell damage (Fireball or Lightning Bolt landed and dealt damage) in recent ticks, **and**
2. Its Ward Power is below a threshold relative to its land size — specifically, Ward Power is too low to have meaningfully resisted those spells (the building investment was not there to help)

Protection duration and strength scale with **how low Ward Power is relative to land size**, not with how many spells landed. A small dominion with no Wizard Guilds that takes repeated war spells gets strong, extended protection. A large military attacker who draws retaliation but has normal building investment does not qualify — their Ward Power is not below threshold.

Magical Fortitude effect: significantly reduces incoming war spell damage and hostile spell success rates. Same protective intent as Rejuvenation but granted to dominions whose building profile indicates structural defencelessness, not those who are simply politically active.

**Magical Fortitude is cancelled if:** the protected dominion invests in Wizard Guilds past the threshold during the protection window. Protection is tied to vulnerability; if you address the vulnerability, the protection ends. This creates a meaningful choice: accept the temporary protection and then invest to prevent future vulnerability, rather than sitting indefinitely immune.

---

## Late-Round Scaling: Arcane Attunement

Military power compounds through land, elites, castle investment, and technology. Magic compounds only through tech and wonders in the current system.

**Arcane Attunement** is a new permanent score (similar to Wizard Mastery in structure) that scales magic into the late round:

**Accumulation sources:**
- Wizard Mastery overflow — once Mastery reaches its cap, subsequent mastery events contribute to Attunement instead
- Sustained Wizard Guild investment — small passive accumulation each tick proportional to current guild land percentage (this means a magic-focused build that has maintained Towers and Guilds all round has naturally accumulated Attunement)
- Successful Cleanse events — dispelling hostile debuffs contributes a small amount, rewarding active defensive engagement

**Attunement effects:**
- Increases base mana production per Tower (stacking with tech, unlike Mastery which reduces cost)
- Increases the duration of self-spell buffs cast, up to a cap
- Provides a small Ward Power bonus as a late-round multiplier

Because Attunement accumulates through building investment and engagement rather than purely through successful offense, it also rewards defensive and non-aggressive magic players who have been consistent all round.

---

## Arcane Impact (Rankings Rework)

The current damage leaderboard rewards raw volume, which incentivizes targeting dominions with no Ward Power rather than strategically significant ones.

Renamed to **Arcane Impact**. Formula changes:

**Per-cast contribution to rankings:**
```
Impact = Base Damage × Ward Difficulty Multiplier × Targeting Premium
```

**Ward Difficulty Multiplier:** Scales with the target's Ward Power at the time the spell was cast. Hitting a well-defended target contributes significantly more to the ranking than hitting a defenceless one. Minimum multiplier of 0.25 (even farming defenceless targets gives some credit); maximum multiplier of 3.0 for hitting the best-defended targets.

**Targeting Premium:** A bonus applied when hitting a target that has recently successfully invaded (i.e., is militarily active and threatening). Represents targeting an actual threat rather than an easy target.

**Diminishing returns:** Each subsequent hit on the same target within a window reduces the Ward Difficulty Multiplier for that target for the next 24 hours. Repeatedly hitting the same easy target gives sharply declining impact credit.

A second ranking column, **Arcane Resilience**, tracks Ward Power sustained and hostile spells resisted. This rewards the defensive dimension of the magic system, which was previously unacknowledged.

---

## Interactions With Other Systems

- **Races & Units** — Races with `counts_as_wizard` unit perks continue to contribute to Spell Power. Immortal wizard perks continue to prevent wizard losses on failed casts. Ward Power is building-based, so races with building efficiency perks gain a modest magical defence advantage.
- **Land & Construction** — Wizard Guilds become the primary defensive magic investment, giving them a dual role: peasant protection (Fireball) and Ward Power contribution. Towers contribute secondarily to Ward Power in addition to mana. Masonry now provides Lightning Bolt protection on parity with Wizard Guilds for Fireball.
- **Population & Resources** — Mana decay and production cadence unchanged. Emergency Ward creates a new mana expenditure valve during snare recovery.
- **Military** — The separation of Ward Power from Spell Power means military-focused dominions can have meaningful magical defence through buildings without sacrificing army composition. Self-buff spells continue to stack onto military multipliers.
- **Espionage** — Illumination (now castable by any realm member) broadens access to spy op protection the same way Arcane Ward access is broadened.
- **Technology** — Tech perks should include options to increase Ward Power per guild or tower, improve Cleanse success rates, and reduce Arcane Bulwark mana cost, giving tech investment real magic-defense relevance beyond just offense.
- **Wonders** — Wonder bonuses to wizard power should apply only to Spell Power, not Ward Power. This keeps wonder investment relevant for offensive magic without further distorting the defensive arms race.
- **Heroes** — Hero perks that currently boost wizard power should split into Spell Power and Ward Power variants. Existing hero perks for spell damage resistance contribute to effective Ward Power.

---

## Player Decision Space

**Wizard Guild vs. Tower vs. Temple (Swamp land):**
Wizard Guilds now carry three distinct values: mana production, Fireball peasant protection, and Ward Power. A dominion that skips guilds entirely is fast-running and offensively unconstrained but has low Ward Power and no peasant protection. Towers maximize mana volume but contribute less Ward Power. This creates more genuine tradeoffs than the current Tower-dominant default.

**Spell Power vs. Ward Power:**
A dominion investing in wizards (Spell Power) without Wizard Guilds (Ward Power) is a glass cannon — effective at casting but easy to hit back. A dominion investing in guilds without wizards is defensively solid but cannot launch meaningful offensive spells. A balanced investment in both requires land allocation to both unit training and building percentages simultaneously — the attacker's dilemma is real, but now it has building-based counterplay rather than being purely population-based.

**Cleanse timing:**
Hostile debuffs now have enough impact that leaving them up is costly. But Cleanse has a cooldown per debuff type. Choosing when to Cleanse — immediately on landing, or after confirming the follow-up spell intent — is a genuine micro-decision that rewards attentiveness.

**Arcane Bulwark scouting problem:**
Because Bulwark is invisible to Revelation, attackers cannot probe it. The cost of probing is now real — if Bulwark is active, your probe is absorbed. Attackers must reason probabilistically about whether a strong target has received a Bulwark cast recently, rather than being able to scout and wait it out. This restores Bulwark's defensive meaning.

**Channel Mana as commitment:**
Casting Channel Mana on a snared realmmate costs your own wizard strength. If you're at the edge of your own snare threshold, you cannot safely help. This makes strength management a team concern — magic-heavy realms must coordinate to avoid everyone being depleted simultaneously.

**Magical Fortitude investment signal:**
The mechanic that ends Magical Fortitude when the target builds Wizard Guilds above threshold is also a strategic signal: if an enemy is under Fortitude, you know their guild investment is low. When it expires, you know either they built up their wards or the protection window ran out. This gives the attacker information about their target's defensive posture over time.

---

## Summary of Issues Addressed

| Issue | Resolution |
|---|---|
| Ratio arms race unwinnable for attackers | Ward Power from buildings replaces wizard ratio as the defensive stat; attackers can invest in guilds without training wizards |
| Fireball/Lightning Bolt counterplay asymmetry | Masonry protection elevated to parity with Wizard Guild protection; Arcane Hardening self-buff adds ratio-independent counterplay for Lightning Bolt |
| Hostile spell debuffs are non-interactive | Cleanse allows Ward-Power-based dispel with cooldown; increased debuff impact makes fighting them worthwhile |
| Team magic locked behind two realm roles | Basic friendly spells (Arcane Ward, Illumination) open to all realm members scaled by Ward Power; roles still superior but no longer exclusive |
| Spell Reflect trivially scouted and probed | Replaced by Arcane Bulwark: invisible, multi-charge, persistent rather than single-use |
| Snare offers zero agency | Emergency Ward self-recovery, Channel Mana ally support, Resilience made visible in UI |
| Magic falls off late round | Arcane Attunement accumulates through sustained investment and engagement, scaling mana output and spell duration into the late round |
| Realm magic strength is luck of the draw | Ward Power from buildings makes magical defence plannable and independent of realm wizard composition luck |
| Rejuvenation protects the wrong players | Magical Fortitude triggers on low Ward Power + received damage — protects structurally defenceless dominions, not politically active ones |
| Damage rankings incentivize targeting the defenceless | Arcane Impact weights hits by target Ward Power and applies diminishing returns on repeated soft targets; Arcane Resilience ranking rewards the defensive dimension |
