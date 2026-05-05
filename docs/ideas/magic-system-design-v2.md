## Magic System — Cohesive Design (v2)

*Second pass built directly from notes. Preserves the open questions in those notes as open questions rather than resolving them. Where the notes diverge from v1, v2 follows the notes.*

---

## Themes

- **End the arms race.** Wizard ratio dominance no longer scales success linearly — power soft-caps near 1.0 WPA/SPA.
- **Penalize real threats more than bystanders.** Black op debuff pressure stacks per realm, so dominant players naturally absorb more pressure.
- **Active defense, not passive stats.** All passive damage reduction (vulnerability, masonry perk, wizard guild peasant protection, rejuvenation) is removed in favor of an active Mana Shield.
- **Teamplay through defensive spells.** A wide bench of self and friendly defensive spells with high variance in mana cost and power level.
- **Specialization without bloating hero tiers.** Specialization perks unlock through Mastery thresholds and realm roles in addition to (or instead of) crowded hero upgrade tiers.

---

## 1. Adjusted Wizard Power

Goal: end the arms race. Cap effective wizard power so that exceeding 1.0 WPA against a target gives steeply diminishing returns, while still rewarding investment.

### Soft Cap Formula

```
Adjusted Wizard Power = 2 × WPA / (WPA + 1)
```

The same shape applies to SPA on the espionage side. The curve approaches 2.0 asymptotically and is centered on 1.0 — at WPA = 1.0 the adjusted value is exactly 1.0, doubling investment past that point gives a much smaller real return.

### Open Questions

- **Do we apply modifiers to Ratio or Power?** Race/tech/wonder/hero/spires perks currently multiply the raw ratio. Decide whether those modifiers continue to apply pre-cap (to raw WPA) or post-cap (to Adjusted Wizard Power).
- **Combine/simplify success formulas.** Whether the existing two-curve split (info vs. hostile/war) collapses into a single formula now that Adjusted Wizard Power softens the gradient.

---

## 2. Non-War Black Op Spells

Goal: penalize real threats more than bystanders. Replace the current single-instance debuff with stackable per-realm debuffs so dominant players accumulate real pressure while smaller targets do not.

### Per-Realm Stackable Debuffs

Each realm contributes one stack of a given debuff. Stacks accumulate to a cap (3–4 realms). A target hit by four realms feels meaningfully more pressure than a target hit by one; no single realm can grief alone.

### Spell Coverage

Existing four (Plague, Insect Swarm, Great Flood, Earthquake) are the baseline.

- **Optional split:** separate the Earthquake effect into ore and gem debuffs.
- **Optional addition:** a lumber-targeting debuff for parity with other resource categories.

### Counterplay

Cleanse is the primary counter (see §4). Stack expiry is per-realm — when a realm stops recasting, their stack drops.

### Open Questions

- **End-of-hour race.** Cleansing right before tick is currently the optimal play. Possible mitigation: cleanse reduces duration to a **minimum of 1 hour** rather than truncating to the cleanse delta, removing the value of last-second timing.
- **Top OP out-ranging.** A dominant player can sometimes drift out of the casting range of weaker realms entirely, defeating the catch-up intent of stacking. Range rules for hostile spells need a pass to make sure the catch-up mechanic actually triggers against the players it's meant to.

---

## 3. Mana Shield

Goal: create an active defense layer that replaces all the removed passive ones.

### Mechanics

- **Always on.** Every dominion has a Mana Shield. There is no toggle.
- **Passive trickle.** The shield charges slowly up to a cap **X**, where X scales with Wizard Power.
- **Self refill.** Casting Mana Shield (self) refills the shield up to a higher cap **Y**, also scaling with Wizard Power. Y > X.
- **Friendly contribution.** A friendly version (Lesser Mana Shield) adds **Z** to an ally's shield, up to their cap.

### Damage Absorption

100 shield = 100% damage reduction for one spell. Larger banks cover more.

| Shield | Coverage |
|---|---|
| 100 | Full block of 1 spell |
| 250 | Full block of 2 spells + 50% of a 3rd |
| 0 | Fully exposed |

### Mana Absorption

Once the shield is depleted, **incoming spells generate mana for the defender**, scaling with the length of the war. Sustained pile-ons against a depleted shield become increasingly self-defeating — the defender absorbs more of the attacker's spent mana the longer the war continues.

### Removed Passives

All passive damage reduction is gone:
- Vulnerability stat
- Masonry passive perk against Lightning Bolt
- Wizard Guild peasant protection
- Rejuvenation status effect

### Open Questions

- **Regen rate.** How fast the passive trickle fills toward X.
- **Decay.** Whether the shield decays over time (to discourage long-term banking) or persists once charged.

---

## 4. Defensive Spells

Goal: a deep bench of active defensive options with **high variance in mana cost and power level** so players make real prioritization decisions under attack. Self-options handle solo defense; friendly versions enable teamplay.

### Self Spells

| Spell | Effect | Cost |
|---|---|---|
| Mana Shield | Refills own shield to max | Expensive |
| Revive | Revives small % of max peasants | Affordable |
| Resurrection | Revives up to 60% of max peasants | Expensive |
| Energy Mirror | High chance to reflect incoming spells | Extremely expensive |
| Repair | Restores % of destroyed castle | Expensive |

### Friendly Spells

| Spell | Effect |
|---|---|
| Arcane Ward | Increases defensive wizard power |
| Illumination | Increases defensive spy power |
| Cleanse | Reduces a random debuff by 2 hours |
| Lesser Mana Shield | Refills small % of ally shield |
| Lesser Revive | Revives small % of ally max peasants |
| Lesser Resurrection | Revives up to 40% of ally max peasants (expensive) — *tentative* |
| Lesser Repair | Restores small % of ally castle |
| Meditation | Increases ally wizard strength regen, but **cancels when the ally casts a spell** (counter to Snare) |

### Friendly Spell Access

Friendly spells should be open to **everyone in the realm**, not gated to roles. They probably need to respect minimum range requirements (the standard 40–250% bracket at minimum) to prevent realm-wide protection on out-of-range allies.

### Open Questions

- **Lesser Resurrection.** Whether to ship the friendly version of Resurrection at all, given Resurrection itself is meant to be expensive and self-focused.
- **Spell Reflect.** Likely **removed**, since Energy Mirror covers reflection probabilistically and the percentage-based mechanic resists probe-consumption better than a one-shot reflect.

---

## 5. Offensive Spells

Goal: clearer counter-relationships between offensive spells and the new defensive bench.

### Spell List

| Spell | Effect | Counter |
|---|---|---|
| Fireball | Kills % of current peasants | Revive / Resurrection |
| Burning | Kills small % of current peasants each hour (DoT) — **LIMITED access** | Cleanse |
| Lightning Bolt | Destroys % of current castle | Repair |
| Targeted Lightning Spells | Destroys % of a specific castle improvement | Repair |
| Lightning Storm | Castle DoT — *tentative* | Repair / Cleanse |
| Ruination | Prevents repairs for 1 hour (expensive) — *tentative* | — |
| Mana Burn | Destroys % of target mana or shield, or increases drain | — |
| Dispel | Reduces duration of a random friendly spell on the target | — |
| Silence | Increases mana cost of friendly spells cast by the target | — |
| Miasma | Counteracts Wards — *tentative* | — |
| Doom | Increases duration of all your active debuffs on the target | — |

### Design Notes

- **Mana Burn** is an explicit counter to Mana Shield and to mana stockpiling more broadly. Exact target (mana pool vs. shield vs. drain rate) is open.
- **Burning** is intentionally **limited** — likely a realm-role spell or otherwise restricted, so it isn't spammed by every member of an aggressor realm.
- **Lightning Storm** as a castle DoT and **Ruination** as a repair-lockout are both candidates for the same anti-repair niche; the design needs to pick one or differentiate clearly.

### Open Questions

- A spell that **prevents revives for one hour** (mirror of Ruination on the peasant axis) is on the table but unspecified.

---

## 6. Specialization

Goal: meaningful magical specialization without forcing every interesting perk onto already-crowded hero upgrade tiers.

### Specialization Axes

| Specialization | Focus |
|---|---|
| Debuffer | Increased debuff duration |
| Opener | Increased damage vs. shield |
| Finisher | Increased damage when shield is down |
| Protector | Better / cheaper friendly spells |
| Cleanser | Better cleanse |
| Healer | Better revives |

### Unlock Channels

Specialization perks can be gated through any combination of:

- **Realm roles** — primarily for perks or whole spells that would be overpowered if every realm member had them (Burning is the clearest example).
- **Mastery thresholds** — e.g. hitting 500 Wizard Mastery unlocks a specialization perk (Path of Destruction, Path of Ruin, etc.). This is the main alternative to stacking perks onto hero tiers, which are crowded and unattractive to non-bloppers.
- **Hero upgrades** — still available as a channel, but no longer the only one.

The intent is that magic specialization can develop organically over a round through play (Mastery) or through realm coordination (roles), rather than only through up-front hero pathing.

---

## 7. Shadow League

Goal: keep the league as a flavor + perk container, but **remove the Chaos spell shenanigans** entirely (chaos score, critical-failure reflects, chaos-only spell access on hostile/war).

### What Stays Open

The other current Shadow/Chaos perks need a fresh pass:
- Info ops access
- Lower losses
- Spell access

Each should be re-evaluated against the new system rather than carried forward by default.

### Open Questions

- **Friendly spell isolation.** Should friendly spells from non-SL realmmates be **disallowed on SL members** unless the caster is also SL? This would preserve the "outsider" identity of the league while preventing the rest of the realm from blanket-buffing SL operatives.

---

## 8. Cleaning Up

Smaller items that fall out of the broader redesign.

### Wizard Guilds

Without peasant protection, Wizard Guilds risk becoming a strictly-worse Tower. Candidate replacement: **wizard strength recovery** boost (returning to an older role). Some races (Dark Elf via Spellwright's Calling) will continue to use them for racial reasons regardless of the generic perk.

### Spy Strength Counter

Disband Spies could be reworked, or a **new spell added** specifically to reduce spy strength — giving wizards a real counter to sinking and snaring on the espionage axis, parallel to how the new defensive bench answers magical pressure.

### Mutual War Perk

Currently mutual war reduces wizard losses on failure. Candidate change: **increased spell damage** during mutual war instead, making escalation a real damage modifier rather than a cushion against bad casts.

### Cyclone & Raid Damage

Both currently use raw WPA × land. Candidate change: switch to **Adjusted Wizard Power × land**, so the new soft cap applies consistently to scaled-damage formulas as well as success rolls.
