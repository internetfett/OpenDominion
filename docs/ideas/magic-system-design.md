# Magic System — Cohesive Design

*Built from brainstorming in magic-system-consolidated.md. This document represents agreed design directions.*

---

## Philosophy and Vision

The new magic system is built around **active decisions over passive stats**. In the current system, magical power is largely determined at build time — wizard ratio, building investment, and race perks establish a static hierarchy that plays out predictably. The redesign shifts the interesting decisions to runtime: what do you cast, when do you cast it, and what do you sacrifice to do so. Mana is the engine for everything — offense, defense, economic buffs, and recovery — and spending it in one area always means not spending it somewhere else. A player under assault diverts mana to their shield and repairs; a player who is not being targeted invests it in economic output or offensive pressure. Magic becomes a continuous resource management game rather than a ratio arms race.

Protection is earned through activity, not optimized through building ratios. The Mana Shield must be actively maintained — a player who reads the threat and responds is genuinely safer than one who does not. Sustained assault is self-limiting through mana absorption, which returns resources to the defender the longer a war drags on, without requiring any resilience stat or special mechanic to trigger. Hostile spell pressure scales naturally with how threatening a target is, concentrating debuffs on dominant players and creating organic catch-up dynamics. The system rewards coordination — realm roles set up amplified war campaigns through Burning and Lightning Storm, realmmates contribute through allied repair and cleanse — while remaining functional for solo players managing their own mana economy.

## Summary of Changes From Current System

### Removed
- **Ratio arms race** — wizard ratio for hostile/war spells capped at 1.0. Exceeding the target's ratio no longer improves success rates.
- **Passive peasant protection** — Wizard Guilds no longer protect peasants from Fireball. All protection is now active.
- **Passive Lightning Bolt protection** — Masonry damage reduction removed. The Mana Shield is the only protection layer.
- **Burning and Lightning Storm as automatic status effects** — reworked into active realm role spells.
- **Rejuvenation as automatic trigger** — reworked as a deliberate response to sustained amplified damage from Burning/Lightning Storm.
- **Lightning Bolt** — replaced by targeted improvement spells hitting specific castle improvements.
- **Wizard Guild peasant protection** — Wizard Guilds now increase wizard strength recovery and amplify shield cap instead.
- **Compounding hostile spell stacking** — single realm can no longer stack unlimited pressure on one target.

### Added
- **Mana Shield** — active castable resource pool that absorbs war spells before real damage lands. 100 shield per spell, proportional penetration.
- **Doctrines** — five magical specializations chosen at creation: Warmage, Hexmaster, Wardcaster, Runesmith, Arcanist.
- **Per-realm hostile spell stacking** — each realm's debuff instance tracked independently, stacking across realms to a cap. Creates natural catch-up mechanic.
- **Cleanse** — castable spell reducing duration of a randomly selected active debuff by 4 hours. 4 hour cooldown.
- **Revive** — restores up to 60% (self) or 40% (allied) of peasants lost to Fireball penetration.
- **Repair Castle** — restores a portion of improvement points destroyed by targeted war spells.
- **Mana Absorption** — innate mechanic returning mana to defenders when spells penetrate the shield. Scales with war duration as the primary anti-griefing tool.
- **Targeted improvement spells** — replace Lightning Bolt with spells targeting specific improvements (Walls, Forges, Science, Keep).
- **Energy Mirror redesign** — 30% chance to reflect any incoming war or hostile spell back to the caster. High mana cost, funded by mana absorption.
- **Tower/Guild split** — Towers produce mana (offensive), Wizard Guilds increase wizard strength recovery and shield cap (defensive).
- **Dual resource costs** — all spells cost both mana and wizard strength in different proportions. Defensive spells are strength-heavy; offensive and economic spells are mana-heavy.

### Modified
- **Info spell formula** — separate adjusted curve with higher success floor. Intelligence broadly accessible regardless of wizard ratio.
- **Wizard losses on failure** — flat predictable cost rather than scaling with ratio mismatch.
- **Burning and Lightning Storm** — now active war-only realm role spells, mutually exclusive. Very strong but bounded by Rejuvenation.
- **Spell Reflect** — restricted to realm roles or Wardcaster doctrine. Not universally available.
- **Energy Mirror** — changed from damage/duration reduction to 30% spell reflection chance.
- **Rankings** — calculated on raw spell damage before shield absorption.
- **Hostile spell values** — reduced per-realm with cross-realm stacking cap rather than single flat debuff.

---

## Success Rates and the Ratio Cap

### Hostile and War Spells

The wizard ratio used in the success formula is **capped at 1.0** relative to the target. Exceeding the target's ratio provides no additional offensive benefit. The interesting defensive variable becomes ward and protection investment, not ratio dominance.

This removes the ratio arms race for offensive casting. The question shifts from "do I outclass their ratio?" to "can I get past their protection layer?"

Wizard losses on failure become a flat, predictable cost rather than catastrophic punishment for attacking a stronger target.

### Info Spells

Info spells remain ratio-based but use a **separate, adjusted formula** with a flatter curve and a meaningfully higher success floor. Even a militarized dominion with few wizards can gather useful intelligence. Ratio still rewards investment but does not lock anyone out.

This makes intelligence broadly accessible — a universal tool — while offensive magic remains a specialist domain gated by protection mechanics rather than ratio.

---

## Doctrines

A specialization chosen **once at dominion creation**, permanent for the round. Layered on top of race abilities — not a replacement. Players who do not choose default to Arcanist with no bonuses or penalties.

| Doctrine | Philosophy |
|---|---|
| Warmage | Specializes in direct magical damage — war spells are their primary weapon |
| Hexmaster | Specializes in sustained hostile pressure — debuffs, duration, and attrition |
| Wardcaster | Specializes in magical defense — protecting self and realm over offensive output |
| Runesmith | Specializes in self-buff efficiency — economic and military buffs at the cost of offensive magic |
| Arcanist | No specialization — full access, no tradeoffs. The default for undecided players |

Doctrines provide bonuses only — no penalties. The tradeoff is implicit: specializing in one area means not gaining bonuses elsewhere. Arcanist is the exception, providing no bonuses but full unrestricted access to everything.

| Doctrine | Perks |
|---|---|
| Warmage | War spell damage bonus; war spells cost less mana; war spells bypass a flat amount of shield per cast |
| Hexmaster | Hostile spell duration bonus; hostile spells cost less mana; their debuffs remove less duration when cleansed |
| Wardcaster | Shield replenishment restores more per cast; higher shield cap; cleanse removes more duration per use; Spell Reflect access |
| Runesmith | Self-buffs cost less mana; self-buff duration extended; mana production bonus |
| Arcanist | No bonuses — full unrestricted access to all spells and mechanics |

*Specific values to be tuned once core mechanics are implemented.*

---

## Hostile Spells

Hostile spells apply debuffs outside of war. Each realm's cast is tracked as an **independent instance** on the target — Realm A's Insect Swarm and Realm B's Insect Swarm are separate, each with their own duration timer. Each realm recasts to maintain their own contribution exactly as in the current system.

### Per-Realm Stacking

Each realm contributes a fixed debuff value per spell type. Multiple realms targeting the same player accumulate to a cap:

- 1 realm: base effect
- 2 realms: 2× effect
- 3 realms: 3× effect
- 4+ realms: capped

Example — Insect Swarm at -5% per realm, capped at -20%:

| Realms targeting | Food production penalty |
|---|---|
| 1 | −5% |
| 2 | −10% |
| 3 | −15% |
| 4+ | −20% (cap) |

### Catch-Up Mechanic

Debuff pressure naturally scales with how threatening a player is. A dominant player targeted by multiple realms accumulates full stacks. A weaker player hit by only one realm sits at the base effect. No single realm can grief a target alone — their contribution is capped at one instance per spell type regardless of how many casters they commit.

### Duration

Each realm manages their own cast duration independently. When a realm stops recasting, their instance expires and their contribution drops off. No shared timer to coordinate.

### Slot Limits

No artificial slot limit. Each realm can have one instance of each spell type active on a target simultaneously — that is the natural limit. Mana cost is the limiting factor, not a cap on variety. Adding a displacement mechanic ("which spell do you want to replace?") creates UI complexity with no design benefit.

If a realm has multiple spell types active on one target, that represents a real ongoing mana investment to maintain. The opportunity cost is self-limiting.

### New Spell Types

The current four economic hostile spells (Plague, Insect Swarm, Great Flood, Earthquake) are sufficient for outside-war debuffing. Any new debuff spells should be **war-only** — requiring an active war declaration. This keeps the hostile spell space simple and contained while giving war meaningful additional tools.

### Cleanse

A castable spell that reduces the duration of a **randomly selected** active hostile debuff. The defender cannot choose which debuff is targeted — this prevents surgical removal of the most damaging effect and keeps the outcome uncertain for both sides.

- **Duration reduction:** −4 hours from the randomly chosen debuff
- **Cooldown:** 4 hours between casts
- **Scope:** Reduces all realms' active instances of the chosen spell type simultaneously
- Realmmates can cast cleanse on allies at slightly reduced effect

The random selection incentivizes attackers to maintain multiple debuff types simultaneously — more active debuffs means a lower chance of the most damaging one being cleansed. However, maintaining more spell types costs more mana, creating a real tradeoff.

The 4-hour cooldown prevents spam. A defender cannot simply throw mana at cleanse until the right debuff is hit — each cast is a considered decision about whether burning the cooldown window now is worth the uncertainty.

*Specific debuff values and caps per spell type to be determined.*

---

## Mana-Based Active Protection

Protection is driven by **active spell casting**, not passive building stats. Players who want magical defense spend mana on it. Players who are not under pressure spend that mana elsewhere — economic buffs, offensive casts, or stockpiling for later. Being targeted imposes a real opportunity cost regardless of whether incoming spells are blocked.

### Mana Shield

A castable spell that replenishes a **Mana Shield** — a separate resource pool that absorbs incoming spell damage. Incoming war and hostile spells must penetrate the shield before dealing real damage to the dominion. A depleted shield leaves the dominion fully exposed.

- Casting the shield spell replenishes the shield resource
- The shield decays or drains as it absorbs hits
- Maintaining protection requires ongoing mana investment
- A player caught with no shield up is genuinely vulnerable

**Shield cap:** The Mana Shield has a maximum capacity. Wizard ratio drives the cap — more wizards means a larger shield ceiling. This gives wizards a clear, legible defensive role without touching success rates. Training wizards is a decision about how much punishment you can absorb, not about casting more reliably. A larger wizard investment answers pile-on pressure from coordinated multi-caster assaults.

This makes protection a continuous decision rather than a build choice. Reading the threat level and casting defensively before an assault is a meaningful skill expression.

### War Spell Interaction

The Mana Shield absorbs incoming **war spells only**. Hostile spell debuffs (Plague, Insect Swarm, etc.) bypass the shield entirely — they are not blocked or reduced by shield value.

Each war spell costs **100 shield** to fully absorb. Damage penetration is proportional to what gets through:

| Shield remaining | Penetration | Example — Fireball (2% peasants) | Example — Lightning Bolt (1% castle) |
|---|---|---|---|
| 100+ | 0% | No damage | No damage |
| 50 | 50% | 1% peasants killed | 0.5% castle destroyed |
| 0 | 100% | 2% peasants killed | 1% castle destroyed |

After absorbing, shield is reduced by whatever it could cover (minimum 0). A shield below 100 means the next spell partially penetrates. A shield at 0 means fully exposed.

**Low shield cap vs. high activity:** The shield cap (driven by wizard ratio) determines how much protection can be banked between sessions. A militarized dominion with a low cap is not necessarily vulnerable — active players recasting each hour keep the shield replenished and maintain full protection. The cap matters most for offline periods: a high cap means more punishment can be absorbed before exposure, a low cap means going offline is riskier.

### Allied Versions

Realmmates can cast a slightly weaker version of defensive spells on each other. A coordinated realm can pool mana toward protecting a priority target. The target's own casting still matters — the allied version supplements but does not replace self-casting.

### Repair Spells

When damage gets through a depleted shield, active repair spells can partially undo the consequences. All passive sources of peasant protection are removed — Wizard Guilds no longer protect peasants from Fireball. Recovery is entirely active.

**Revive** — Restores lost peasants after a Fireball penetration:
- Self-cast: recovers up to 60% of peasants lost from that hit
- Allied cast: recovers up to 40% of peasants lost from that hit
- Both can contribute but recovery is capped at 60% total per hit

**Repair Castle** — Restores a portion of improvement points destroyed by targeted war spells. Specific recovery cap to be determined. Likely targeted per improvement type to mirror the offensive spells.

Repair is partial — the attacker's work remains meaningful and sustained assault is not futile. Under active attack, realmmates face real prioritization decisions: replenish the shield, revive peasants, or repair the castle.

### Mana as Opportunity Cost

A player not under attack uses mana offensively or economically. A player under sustained assault must divert mana to defense. The attacker imposes real cost even when spells are absorbed — draining an opponent's defensive mana is its own form of pressure, degrading their economy or offensive output without dealing direct damage.

### Mana Absorption

When a spell **penetrates the shield and deals real damage**, the defender absorbs a portion of mana back. Spells fully absorbed by the shield generate nothing — if you're not taking damage, you don't need the resource.

The absorption amount increases with war duration. Early in a war, penetrating hits return a small amount. As the war drags on, each penetrating hit returns progressively more. Sustained pile-ons become increasingly self-defeating — the attacker keeps spending mana to deal damage, the defender absorbs more and more of it back to replenish their shield and fund repairs.

This is the primary anti-griefing mechanic. No resilience stat required — just a resource flow that naturally favors the defender the longer a war continues. Mana absorption is an **innate system mechanic** applying to all dominions regardless of build — it is not tied to Wizard Guilds or any other building investment.

### War Spell Simplification

The following mechanics are removed entirely:
- **Burning** and **Lightning Storm** status effects — removed. No more compounding amplification or snowball dynamics.
- **Rejuvenation** — removed. The edge cases and diplomatic complications it created are no longer needed.
- **Passive Lightning Bolt protection** (Masonry damage reduction) — removed. All passive war spell protection is gone. The Mana Shield is the only protection layer.

### Targeted Improvement Spells

Lightning Bolt is replaced by a suite of targeted war spells, each hitting a **specific castle improvement** rather than improvements broadly. Attackers choose what to damage strategically. Each costs 100 shield to absorb, with proportional penetration as per the Mana Shield model.

Candidate spells (names provisional):

| Spell | Target |
|---|---|
| Crumble | Walls |
| Melt | Forges |
| Corrupt | Science |
| Undermine | Keep |

Spires and Harbor may remain exempt or become targets — to be determined. Repair Castle spells mirror this structure, targeting the same specific improvements.

### Burning and Lightning Storm — Active Realm Role Spells

Burning and Lightning Storm are redesigned as active war-only spells restricted to realm roles. They are **mutually exclusive** — a realm can only have one active at a time, forcing a campaign identity decision before war begins.

- **Burning** — while active, all realm members' Fireballs deal significantly amplified damage against the target
- **Lightning Storm** — while active, all realm members' targeted improvement spells deal significantly amplified damage against the target

Both are intentionally very strong. The natural counterweight is **Rejuvenation** — after absorbing sufficient amplified damage, the target enters a Rejuvenation phase where incoming war spell damage is reduced for a period. This creates a natural two-phase war rhythm:

1. **Assault phase** — Burning or Lightning Storm active, amplified damage window. Attackers maximize damage before the clock runs out.
2. **Recovery phase** — Rejuvenation active, damage reduced. Target recovers. Attackers regroup.

Smaller realms benefit from this structure — they can do comparable damage to larger groups by concentrating their assault into the amplified window rather than sustaining indefinitely. The mutual exclusivity rewards pre-war coordination: committing to Burning means running a peasant and food destruction campaign; Lightning Storm means running an infrastructure destruction campaign.

### Energy Mirror

A high mana cost self-spell providing a **30% chance to reflect** any incoming war or hostile spell back to the caster. Reflected spells resolve against the caster's own shield before dealing damage.

Designed as a reactive tool — the high mana cost means it is situational rather than always-on. The mana absorption mechanic naturally funds it: sustained assault generates absorbed mana, which can be spent on Energy Mirror to create reflection risk for the attacker. The longer the assault continues, the more likely the defender can afford it.

Unlike Spell Reflect, the percentage-based mechanic cannot be probe-consumed. Even if the attacker scouts it active via Revelation, the 30% risk persists for the full duration. Every cast is a gamble.

Reflected damage applies to the caster's own shield first — reflection is not free damage immunity for the defender.

Natural Wardcaster perk candidate — higher reflection chance or reduced mana cost.

### Friendly Spells

Existing friendly spells remain as-is. **Spell Reflect** is kept but restricted — available only to specific realm roles or as a doctrine perk, not universally castable. Other friendly spells (Arcane Ward, Illumination) are unchanged.

---

## Perk and Upgrade Design Space

The following modifier categories are available for doctrines, racial spells, technology, and heroes to draw from. Each system should pull from distinct areas to avoid overlap.

### Damage Modifiers
- Bonus damage (flat %)
- Bonus damage vs depleted shields
- Bonus damage while a specific hostile debuff is active on target
- Bonus damage during mutual war
- Bonus damage after a successful invasion
- Bonus damage to a specific improvement type (walls, forges, etc.)
- Bypass a flat amount of shield per cast

### Hostile Spell Modifiers
- Extended duration
- Harder to cleanse (opponent removes less duration per cleanse)
- Higher per-realm stack cap
- Bonus effect when multiple hostile spell types are active simultaneously

### Defensive Modifiers
- Higher shield cap
- Shield replenishment restores more per cast
- Passive shield trickle
- Cleanse removes more duration per use
- Cleanse cooldown reduced
- Repair recovery cap increased (e.g. 60% → 70%)
- Spell Reflect access

### Cost Modifiers
- War spells cost less mana
- Hostile spells cost less mana
- Defensive spells cost less wizard strength
- Self-buffs cost less mana

### Conditional Modifiers
- Bonus damage when target shield is fully depleted
- Stronger cleanse when at high wizard strength
- Cheaper casting when own shield is full
- Bonus repair while actively under attack

### Mana Absorption
- Absorb more mana per penetrating hit
- Absorption scales faster with war duration

### Allied / Realm
- Allied defensive spells are stronger
- Allied cleanse is more effective
- Allied repair spells restore more

---

## Rankings

Offensive magic rankings are calculated on **raw spell damage before shield absorption**. A Fireball worth 2% peasants scores the same whether the shield absorbed all of it or none of it. This removes any incentive to specifically target unshielded or weakly defended players for ranking purposes — attacking a well-defended opponent earns equal credit.

---

---

## Wizard Guilds and Towers

Towers and Wizard Guilds serve distinct, non-overlapping roles creating a clear offensive/defensive building tradeoff. Both compete for the same land type, making the split a meaningful positional decision.

**Towers** — produce mana. Mana fuels offensive and hostile spells (war spells, Fireball, targeted improvement spells, hostile debuffs). The offensive building investment.

**Wizard Guilds** — increase wizard strength recovery rate and amplify the shield cap provided by wizards. The defensive building investment.

### Resource Split

All spells cost both mana and wizard strength, but in different proportions depending on spell category. Snare (low wizard strength) affects all casting but hits strength-heavy spells hardest. Snare does not remove active spells — only prevents recasting.

| Spell category | Mana cost | Wizard Strength cost |
|---|---|---|
| War spells (Fireball, targeted improvements) | High | Low |
| Hostile spells (Plague, Insect Swarm, etc.) | High | Low |
| Defensive spells (shield, cleanse, revive, repair) | Low | High |
| Self-buffs (Midas Touch, Ares' Call, etc.) | High | Minimal |

Towers (mana production) primarily enable offensive and economic casting. Wizard Guilds (wizard strength recovery) primarily enable defensive casting. A dominion investing heavily in guilds can sustain more frequent defensive spell casting; one investing in towers can sustain more offensive pressure.

### Shield Cap

Wizards provide the base shield cap. Wizard Guilds amplify that cap — more guilds means the wizard-derived ceiling is raised further. A militarized dominion with few wizards but many guilds achieves a reasonable cap. A magic-focused dominion investing in both achieves a large one.

Faster wizard strength recovery from guilds directly enables more frequent defensive casting — the guild investment pays off through sustained defensive activity, not passive stat bonuses.
