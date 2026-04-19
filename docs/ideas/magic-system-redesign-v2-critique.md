# Magic System Redesign v2 — Design Critique
*Analysis against the identified issues, with emphasis on complexity*

---

## Executive Summary

The redesign correctly identifies and fixes the most structurally damaging problems in the current system. The separation of offensive capability (Spell Power) from defensive capability (Ward Rating), the elimination of wizard death on failure, the removal of mana decay, the Stance system replacing passive debuff endurance, and individual Hostile Acts replacing realm-wide war requirements — these are the right surgeries on the right problems.

However, the redesign trades one set of problems for another. Where the current system suffers from opacity (an opaque success curve, counterintuitive interactions), the redesign suffers from **surface area explosion**. It introduces more resource types, more tracking dimensions, more decision layers, and a new meta-progression axis (School Attunement) that touches every spell cast — all while leaving several critical issues explicitly unaddressed.

This document evaluates the redesign against the 67 identified issues, flags where complexity is added without proportional strategic depth, and proposes specific modifications.

---

## Part I: What the Redesign Gets Right

### Critical Issues Resolved

**Issue #1/#2 — Self-reinforcing ratio system.**
The fundamental fix: separating Spell Power (offensive) from Ward Rating (defensive) into independently funded stats. A player who invests in WR can cut incoming damage by 55% without affecting whether spells succeed — and that doesn't make them harder to cast against. The runaway leader dynamic is severed. This is the most important single change in the document and it's executed cleanly.

**Issue #3/#4 — The 1% floor and 97-98% ceiling.**
Replaced with legible success tables that players can actually read. A fully committed attacker knows they're at ~78% hostile / 68% war spell success. A non-specialist knows they're at ~35-55%. The curve is explicit. This is unambiguously better.

**Issue #6/#60 — Mana decay.**
Removed entirely. The Surge Charge overflow mechanic rewards patient casting without penalizing inactivity. Strategic stockpiling (via Surge Charges) is now viable. This is an excellent design move — it replaces a passive punishment with an active reward.

**Issue #14/#41 — No dispel, no counterplay.**
The Stance system is the redesign's most elegant contribution. Pre-configured automatic responses (Absorb, Diminish, Dispel) mean the offline player is never helpless. The 60-second Active Override rewards online presence without excluding offline players. The Ward Charge reservoir creates a resource management decision around defense rather than a passive waiting room. This directly solves issues #14, #41, #49.

**Issue #27/#44 — War spells require realm-level war.**
Individual Hostile Acts earned through being attacked or retaliating is exactly the right solution. Magic-focused players can now act on their own diplomatic history without requiring realm political consensus. This also opens interesting emergent gameplay: a Hexblade who is aggressively blighting you has just given you personal Fireball access against them.

**Issue #21 — Non-stacking buff waste.**
The UI confirmation prompt when casting a lower-value same-type buff is a simple, correct fix. Players make informed decisions. This should have always existed.

**Issue #45/#18 — Info spells produce stale snapshots.**
Persistent Wards (live-updating intelligence) are a strong concept that elevates the School of the Unseen from a vestigial system into a genuine tactical advantage. The Seer Mastery Path making Fate Sight hit an entire enemy realm simultaneously is the kind of team-play payoff that justifies investing in an intelligence role.

**Issues #52, #53, #54, #56 — Racial spell imbalances.**
The targeted redesigns are mostly good. Erosion becoming player-selected rezoning (not automatic) is correct. Gaia's Light/Shadow removing mutual penalization is correct. Death and Decay requiring explicit mode selection is correct. The Resolute Archmages redesign (defensive WR generation rather than the now-obsolete immortal wizard perk) is sensible.

**Issues #19/#65 — Mana is siloed / magic builds must commit early.**
Ley Shift (Arcanist exclusive) addresses the mana-sharing gap. Surge Charges as an overflow valve reduce the mandatory tower-building pressure somewhat.

---

## Part II: Issues Not Addressed — The Gaps

### Critical Gaps

**Issue #17/#24 — Rejuvenation cancels active war. Three degrees of indirection.**
The redesign preserves the Burning → Rejuvenation → war cancellation chain verbatim. This is one of the most diplomatically disruptive mechanics in the system and it appears untouched. The war-cancellation consequence of buff expiry is unintuitive, invisible to most players, and removes agency over a realm-level diplomatic decision through a mechanical timer. This needed a direct decision — either remove the war-cancellation effect from Rejuvenation, or promote it to an explicit diplomatic mechanic ("Rejuvenation suppresses war bonuses but does not cancel the declaration"). The silence here is the redesign's largest omission.

**Issue #15/#42 — Friendly spells restricted to two realm roles.**
The Mastery Path Realm Auras are an excellent partial solution — they provide passive magical contribution from any player holding a Path, regardless of title. But *targeted* ally spells (Arcane Ward, Ley Anchor, Sanctum Shield) remain locked to Grand Magister / Court Mage. In a 12-person realm, 10 players still cannot cast protective spells on a realmmate under assault. The Warden's Grand Sanctum helps with realm-wide coverage but doesn't substitute for targeted protection. Issue #61 (single points of failure if these role-holders go inactive) is also not addressed.

**Issue #25 — Disband Spies.**
This hostile spell converts enemy spies to draftees — a cross-system hard counter that can undo an entire espionage investment with a single cast. The redesign's Ruin school replaces most hostile spells with Blight Stacks, but Disband Spies is not mentioned at all. Is it removed? Converted to a Ruin effect? Preserved unchanged? The omission is notable because it's a critical issue in the original document and the redesign's Blight Stack framework doesn't have an obvious analog for it.

**Issue #29 — Mana cost still scales with total land.**
The redesign states "Spells cost mana scaled by total land size." The moving-cost-target problem for growing dominions is preserved unchanged. Late-round spell budget planning is still difficult for players in active growth phases.

**Issue #47/#20 — 3-day hostile spell restriction.**
The redesign doesn't explicitly address whether the 3-day hostile casting restriction is removed, modified, or replaced. The Protection period implicitly provides some early-round barrier, but the original problem — a sudden floodgate opening on Day 3 that catches unprepared players before they've cast defensive self-buffs — isn't treated.

### Notable Missing Integration

**Technology tree interaction.**
The current system has numerous tech perks that reduce spell costs, extend spell durations, increase wizard power, and improve wizard strength recovery. The redesign introduces School Attunement as a new progression axis. How do existing tech perks map? Does tech still grant mana cost reduction, or does it now increase Attunement tiers? Does a tech perk that previously gave "+10% wizard power" now give "+10% Spell Power"? This integration gap is significant — the entire tech layer is unaddressed.

**Hero perk interaction.**
Certain hero perks currently modify wizard power, spell damage output, spell damage resistance, and self spell strength costs. None of these are addressed in the redesign. If Spell Power and Ward Rating are the new stats, hero perks need explicit remapping.

**Chaos League and Black Guard.**
The redesign eliminates random critical failure self-reflection (correctly). But the Chaos system as a whole — Chaos score, chaos accumulation, cross-member war spell access, Delve into Shadow — is not addressed. The Black Guard's current role in enabling war spells and friendly spells without formal war is also unaddressed. Issue #30/#62 (Chaos being exclusive to one org) remains unsolved because the Chaos system's future is undefined.

---

## Part III: The Complexity Problem

This is where the redesign needs the most work.

### The Resource Count Doubles

The current system has three primary resources that players track: **mana** (fuel), **wizard strength** (stamina), and **wizard ratio** (power level). Everything else derives from these.

The redesign introduces: **Mana, Surge Charges, Ward Charges, Spell Power, Ward Rating, School Attunement (×4 schools, ×4 tiers), Arcane Overload, Blight Stacks (×5 types, ×3 per type, per target)**.

That is not a simplification. The Core Vocabulary section alone lists 14 terms. For a system that is supposed to fix the original system's opacity, this vocabulary explosion is a serious design concern. Complexity is not inherently bad — the question is whether each layer creates interesting decisions or just bookkeeping load.

### School Attunement Creates a Hidden Multiplier on Everything

Four schools. Four tiers per school (Novice, Adept, Master, Grandmaster). Each tier produces different damage, duration, cost, and special effects. This is a 4×4 effectiveness matrix applied to every spell cast.

The power gap between attunement tiers is extreme. Consider School of Devastation:
- Novice: −25% damage, +25% mana cost
- Grandmaster: +30% damage, Empower ignores 30% WR, 20% Empower crit chance

That's roughly a **68% effective power difference** between the bottom and top tier. In the current system, the largest analogous gap (between races with immortal wizards vs. those without) was flagged as Issue #53 — a "fundamental asymmetry." The attunement system potentially recreates that gap as a permanent feature of every school, every cast.

The problem compounds because attunement level is determined by race + buildings + tech + Mastery Path. Players cannot read their effective spell output from any single stat. They must know their race's starting attunement, whether they've built enough Wizard Guilds, what tech they've researched, and what Path they chose. This is the same opacity problem the redesign is trying to fix, recreated at a higher level.

**Suggested modification:** Collapse to two tiers — Standard and Attuned. Standard is the default for all players. Attuned is granted by Mastery Path in that school plus a modest building/tech threshold. The effect of Attuned is a single clearly communicated bonus (+20% effectiveness, fixed). This retains differentiation without a 4-tier hidden multiplier.

### Blight Stack Management is Micro-Intensive

The Ruin school's Blight Stack system is conceptually elegant — sustained pressure requiring maintenance effort. In practice, a Hexblade maintaining maximum stacks on a serious target must:
- Cast 1 Ruin spell per tick per Blight type to maintain 3 stacks
- Track 5 independent Blight types, each decaying separately
- Account for the target's Ward Charge pool and Stance (will the stacks land?)
- Manage their own Mana and Surge Charge budgets simultaneously

At full deployment across even 2 targets, a Hexblade could be managing 30 Blight Stack instances across 10 spell types with independent decay clocks. This is a spreadsheet, not a game mechanic.

The Hexblade Path's Withering Pall exclusive (doubles all active stacks, resets decay) helps — but it costs 2 Surge Charges and requires stacks to already be present. It reduces the maintenance burden for burst moments but doesn't address steady-state bookkeeping.

**Suggested modification:** Simplify to one unified "Ruin Blight" per target rather than 5 independent types. Each Ruin spell contributes to the same stack pool (different spells emphasize different debuff effects per stack, but they share a stack counter). Maximum 5 stacks. This preserves the sustained-pressure mechanic without requiring per-type tracking. Alternatively, set decay to 1 per tick globally and allow any Ruin cast to reset all active stacks on that target — reducing the cast-per-type burden significantly.

### Surge Charges Have Competing Priorities That Are Under-Explained

Surge Charges are pitched as enabling "decisive moments" — the patient caster saving up for an Empowered strike. But the redesign also specifies: "Spending a Surge Charge on a failed cast negates the Overload entirely — stored power insulates the caster."

This creates two competing reasons to spend Surge Charges:
1. Offensively: Empower a spell for amplified effect
2. Defensively: Insulate yourself from Arcane Overload on a failed cast

The insurance function isn't mentioned in the Player Decision Space section at all, despite being a significant tactical choice. Should a player save Charges for high-impact Empowered strikes, or burn them reactively when casts start failing? This is an interesting decision — but it's buried in the Arcane Overload section rather than presented as a core resource management question.

**Suggested modification:** Make this tension explicit. Add it to the Player Decision Space section as a named decision: "Surge Charge as insurance vs. amplification." The mechanic is good. The communication of it is not.

### The Arcane Congress Is a Full Sub-Game

The Congress mechanic — call, 4-hour contribution window, pool threshold selection, spell cast — is well-designed as a team coordination payoff. But it requires:
- The Grand Magister or a Mastery Path holder to initiate
- All realm members to contribute within 4 hours
- Pool calculation against 5 possible spell tiers
- Caller selection among available spells

This is a 4-step process with resource allocation, communication coordination, and timing sensitivity. It's engaging for hardcore, coordinated guilds. For a 12-person realm with mixed activity schedules across multiple time zones, hitting the 4-hour window reliably is a significant logistical barrier.

The system also rewards the wealthiest realms (most mana to contribute) and the most coordinated realms (can reliably hit windows). These are already the strongest realms. The Congress amplifies existing advantages while remaining largely inaccessible to less-organized groups.

**Suggested modification:** Extend the contribution window to 12 hours. Reduce the once-per-72h cooldown to once-per-48h. This widens participation without removing the coordination requirement. Alternatively, allow Congress contributions to be "pledged" — members commit mana they'll contribute at next tick without needing to be online simultaneously.

### The Arcane Saturation Anti-Griefing System Creates an Exploit Window

The Arcane Immunity threshold (15+ spells from one realm in 24h) is important and correct — sustained magical assault should have diminishing returns. But the implementation has a structural problem.

When Immunity triggers, it lasts 12 hours and then **Saturation resets to zero**. An attacking realm knows exactly when Immunity expires. They also know that after Immunity ends, the target is at Saturation zero — meaning the first 3 incoming spells again face no resistance. Coordinated attackers can:
1. "Burn" the Immunity with 15 cheap spells
2. Wait 12 hours
3. Launch a high-value assault starting from Saturation zero

The intended protection window becomes a predictable timing loop that experienced players will exploit rather than be deterred by.

**Suggested modification:** After Immunity expires, Saturation does not reset to zero — it resets to Low (4-6 spell threshold). This means the target never drops below the first tier of protection within a 24-hour window once assaulted. Complete reset requires 24 hours of no incoming spells from that realm, not just the immunity expiration.

### Mastery Path by Day 7 is Too Early

The Mastery Path is a permanent, round-defining choice made by Day 7. To make an optimal choice, a player needs to assess:
- Which Paths their realmmates are likely to choose (realm coordination)
- Which Paths the enemy realm is likely to use (intelligence)
- How their own race's attunement profile interacts with each Path
- Which Realm Aura benefits their specific play pattern

Day 7 is during the early round. Protection periods are still ending. Most players haven't established their economic foundation, can't assess their realm's military posture, and likely have no intelligence on enemy realms. This is the worst possible moment to make a permanent system-defining choice.

**Suggested modification:** Move the deadline to Day 14. This falls in the early mid-round phase when players have had time to assess realm composition, see emerging threats, and coordinate with realmmates. The choice is still early enough to define the round's identity while being late enough to be informed.

---

## Part IV: Issue-by-Issue Assessment

| # | Issue | Addressed? | Notes |
|---|---|---|---|
| 1 | Ratio system creates runaway leaders | YES | SP/WR separation is the core fix |
| 2 | Wizard losses on failure punish weak casters | YES | Replaced with Arcane Overload (temporary, no death) |
| 3 | 1% success floor creates unavoidable damage | YES | Fixed success tables with no floor below visible baseline |
| 4 | 97-98% ceiling means dominant casters randomly fail | YES | 78% / 68% hard caps with transparency |
| 5 | Racial spells cost 5× mana with no flexibility | PARTIAL | Mana costs still scale; attunement helps in-school races |
| 6 | Mana decays 2% per tick | YES | No decay; Surge Charges as overflow |
| 7 | Hostile durations extend in war (compounds losing) | PARTIAL | Arcane Saturation limits sustained assault but war duration extension not addressed |
| 8 | 80% war spell damage reduction cap | YES | WR mitigation curve replaces the hard cap; full WR investment = 65% reduction (no artificial floor) |
| 9 | Wizard Guilds serve two unrelated functions | YES | Guilds → WR/Ward Charges (defensive only); Towers → Mana (unchanged) |
| 10 | Tower/Guild/Temple land competition forces extremes | PARTIAL | Guild now clearly defensive; land competition still exists |
| 11 | Being snared offers zero counterplay | YES | Arcane Overload is temporary; Resilience retained; Stance system still works during Overload |
| 12 | Spell Reflect inflicts self-damage | YES | Sanctum Shield replaces reflection with negation; amplified reflection removed |
| 13 | Chaos critical failure reflects own spell | YES | Critical failure self-reflection eliminated |
| 14 | No dispel mechanic | YES | Stance system: Dispel removes effect entirely |
| 15 | Friendly spells restricted to 2 roles | PARTIAL | Realm Auras help; targeted ally spells still role-gated |
| 16 | Arcane Ward/Illumination cooldown creates vulnerability windows | PARTIAL | Sanctum Shield is refreshable; cooldowns remain on other spells |
| 17 | Rejuvenation war-cancellation is unintuitive | NO | Preserved unchanged |
| 18 | Info spells produce stale snapshots | YES | Persistent Wards are live-updating |
| 19 | Mana cannot be shared | PARTIAL | Arcanist's Ley Shift: 25% transfer once per 24h |
| 20 | 3-day hostile spell restriction | UNCLEAR | Not addressed |
| 21 | Same-type buff non-stacking with no UI warning | YES | UI confirmation prompt on override |
| 22 | Amplify Magic is shallow | YES | Replaced by Surge Charge Empower mechanic with meaningful choices per school |
| 23 | Burning/Lightning Storm snowball dynamics | PARTIAL | Retained but modified; Grandmaster-only crits reduce randomness |
| 24 | Status effect expiry cancels war | NO | Rejuvenation chain preserved |
| 25 | Disband Spies hard-counters espionage | UNADDRESSED | Not mentioned |
| 26 | Incite Chaos is overcomplicated | UNADDRESSED | Espionage-side; not in scope |
| 27 | War spells require realm-level war | YES | Hostile Acts provide individual credit |
| 28 | Wizard strength vs. wizard ratio naming confusion | YES | New vocabulary (SP, WR) is clearer, though still requires learning |
| 29 | Mana cost scaling makes planning difficult | NO | Land-scaled cost preserved |
| 30 | Chaos system exclusive to Chaos League | UNADDRESSED | Chaos system future undefined |
| 31 | Cyclone double-damages unowned wonders (perverse incentive) | PARTIAL | +30% damage during mutual war only — removes the "destroy your own wonder" perk |
| 32 | Critical success and failure create extreme variance | YES | Crits locked to Grandmaster Empower; random crits removed for others |
| 33 | Spell Reflect amplified damage has no rationale | YES | Removed; Sanctum Shield negates instead |
| 34 | Rejuvenation cancels war — three degrees of indirection | NO | Preserved |
| 35 | Archmages count as 2× in ratio (hidden factor) | PARTIAL | Archmages redesigned as defensive-only; hidden 2× conversion removed but new formulas replace it |
| 36 | Wizard mastery provides vague bonuses | PARTIAL | Mastery replaced by School Attunement which has explicit tiers but its own opacity issues |
| 37 | Resilience is counterintuitive | PARTIAL | Resilience retained but its role is now smaller (Overload recovers passively anyway) |
| 38 | Friendly spell cooldown timers unclear | PARTIAL | Cooldowns mentioned but UI timer communication not explicitly improved |
| 39 | Success formula is opaque exponential curve | YES | Explicit success tables replace the opaque formula |
| 40 | Energy Mirror affects damage AND duration for different spell types | PARTIAL | WR mitigation handles both; more unified but still type-dependent |
| 41 | Fool's Gold only protects platinum by default | UNCHANGED | Tech unlock for full coverage preserved |
| 42 | Non-role realm members cannot contribute magically | PARTIAL | Realm Auras help; targeted spells still role-gated |
| 43 | No magical support for allies under military pressure | NO | No military-support spells added |
| 44 | War spells require realm-level war | YES | Individual Hostile Acts |
| 45 | Info spells produce stale intelligence | YES | Persistent Wards |
| 46 | Surreal Perception has no counter | YES | Mana Veil hides caster identity from Surreal Perception |
| 47 | 3-day restriction creates sudden opening | UNCLEAR | Not addressed |
| 48 | Spell Reflect easily circumvented | YES | Sanctum Shield is not snapshot-based; refreshable |
| 49 | Snare removes player from competition for ticks | YES | Arcane Overload is temporary and non-lethal |
| 50 | Hostile spells too marginal to justify investment | PARTIAL | Blight Stacks more meaningful but impact math needs validation |
| 51 | Unholy Ghost categorically more powerful than other racials | PARTIAL | Reclassified as Draftee Blight (50% contribution, 10h, non-stackable); reduced from binary to scaled |
| 52 | Erosion may fight against player's own build | YES | Player selects which acres to rezone |
| 53 | Immortal wizard perk creates asymmetry | YES | Redesigned as Resolute Archmages (+50% WR per archmage) |
| 54 | Wood Elf Gaia's Light/Shadow mutually penalizing | YES | Penalties removed; pure buff choice |
| 55 | Racial spell costs peak when competition peaks | PARTIAL | No decay helps; land-scaled cost issue persists |
| 56 | Death and Decay can trap new players | YES | Explicit mode selection with separate prompts |
| 57 | Magic investment cannibalizes military population | PARTIAL | Archmages now defensive-only (clearer tradeoff); fundamental population competition unchanged |
| 58 | Wizard mastery rewards the already-strong | PARTIAL | School Attunement through successful casts has similar compounding risk |
| 59 | No defensive war spells | PARTIAL | Stance system is defensive; no active defensive war magic added |
| 60 | Mana decay removes stockpile strategy | YES | No decay; Surge Charges enable strategic reserves |
| 61 | Role restriction creates single points of failure | PARTIAL | Warden Path's Grand Sanctum partially compensates; targeted spells still role-gated |
| 62 | Chaos system fun only for Chaos League | UNADDRESSED | Chaos system future undefined |
| 63 | Spell Reflect probe-exploitable | PARTIAL | Sanctum Shield is refreshable; probe-then-assault still viable because there's no rapid recast on consumed shields |
| 64 | Wizard strength recovery is hard-capped | PARTIAL | Arcane Overload recovers passively with no explicit cap issue mentioned |
| 65 | Magic builds must commit early (Towers) | PARTIAL | Less pressure with no decay; fundamental Tower commitment unchanged |
| 66 | Magic has no equivalent to military morale | YES | Blight Stacks and School Attunement add strategic depth; Ward Charge management is morale-analogous |
| 67 | Late-round magic falls behind military | PARTIAL | Mastery Path + Realm Auras provide late-round scaling; fundamental power gap not addressed |

---

## Part V: Priority Modifications

Listed in order of impact.

### 1. Address Rejuvenation's War Cancellation (Critical — Issue #17/#24)

This is the largest omission. Remove the war-cancellation effect from Rejuvenation entirely. Replace it with "Rejuvenation suppresses war bonuses (damage amplification, duration extension) while active, but does not cancel the war declaration." The diplomatic consequence belongs in the realm's hands, not in a spell expiry timer.

### 2. Fix Arcane Saturation's Reset Vulnerability (Critical)

After Arcane Immunity expires, Saturation resets to Low (not Zero). Full reset to Zero requires 24 hours with no incoming spells from that realm. This prevents coordinated attackers from treating immunity as a predictable "clear" that opens an attack window.

### 3. Move Mastery Path Deadline to Day 14 (High)

Players cannot make informed Path choices by Day 7. They don't yet know their realm's composition, their enemies' approaches, or their own economic trajectory. Day 14 is still early enough to define the round while allowing informed decisions.

### 4. Collapse Blight Stack Types (High)

Replace 5 independent Blight types (each tracked separately, each capped at 3, each with independent decay) with a unified Ruin Blight per target capped at 5 stacks. Different Ruin spells emphasize different debuff effects per stack (Plague: population growth; Insect Swarm: food; etc.) but contribute to the same stack pool. This preserves the spell variety and sustained-pressure mechanic while eliminating per-type tracking.

### 5. Simplify School Attunement to Two Tiers (High)

Reduce from 4 tiers (Novice/Adept/Master/Grandmaster) to two: Standard and Attuned. Standard is the default. Attuned is granted by Mastery Path in that school (or by meeting a building + tech threshold without a Path). Attuned grants a clear, single bonus per school:
- Ruin Attuned: Blight Stacks decay 1 tick later; 10% mana cost reduction
- Devastation Attuned: +20% damage
- Unseen Attuned: Persistent Wards accessible; Omen Reading reveals source caster
- Warding Attuned: +10% WR; Arcane Ward grants +35% WR (up from +25%)

This eliminates the 4×4 effectiveness matrix without removing attunement as a concept.

### 6. Make the Surge Charge Insurance Trade-off Explicit (Medium)

Add "Surge Charges as insurance vs. amplification" to the Player Decision Space section. Frame it clearly: spending a Charge on a failing cast prevents Overload penalty; spending it on a successful cast empowers the effect. A player who knows they're overextending offensively might spend Charges to absorb the failure cost. This is an interesting decision that deserves to be front-and-center.

### 7. Extend Arcane Congress Contribution Window (Medium)

Extend from 4 hours to 12 hours. The 4-hour window strongly favors tightly coordinated groups in compatible time zones. 12 hours widens participation without removing the coordination requirement. Alternatively, implement async pledging: members commit mana to the next Congress call whenever they're online, and the pool accumulates between calls rather than requiring simultaneous participation.

### 8. Resolve Disband Spies (Medium)

Make an explicit decision: either remove Disband Spies, or convert it to a Ruin Blight analog (Spy Blight: reduces effective spy power by X% per stack for Y hours). A cross-system hard counter that can destroy an entire espionage investment with a single cast has no equivalent in the redesign's framework and needs a resolution.

### 9. Remove or Redesign the Rejuvenation War-Bonus Mechanic (Medium)

Burning and Lightning Storm currently amplify subsequent war spell damage. This creates the "spam Fireball after the first lands" snowball dynamic (Issue #23). If Burning/Lightning Storm are retained in the redesign, cap their damage amplification to +25% (down from current levels) and make the amplification flat rather than compounding. The forced Rejuvenation cooldown is a good rhythm-maker; the escalating damage ceiling within the Burning window is the problem.

### 10. Add Tech and Hero Integration Notes (Medium)

The redesign cannot be evaluated or implemented without knowing how the tech tree and hero perk layers map to the new stats. A one-page integration appendix that specifies: existing "+wizard power" tech → "+SP%"; existing wizard strength recovery tech → "+Overload recovery rate"; hero perk remapping. Without this, the redesign is mechanically incomplete.

---

## Part VI: Overall Assessment

The redesign is the foundation of a genuinely better magic system. Its core structural decisions — splitting SP from WR, removing wizard death, eliminating mana decay, building the Stance system, creating individual Hostile Acts — address the most game-damaging issues correctly and with clear design logic.

The problem is execution: too many resources, too many tiers, too many independent tracking dimensions, and several critical issues left unresolved. A system that replaces 6 core concepts with 14 named terms and adds a 4×4 attunement matrix has not necessarily become more interesting — it may simply have become more complicated.

The redesign should be tightened by:
- Collapsing the Blight Stack system from 5 independent type-tracked pools to a unified per-target stack
- Reducing School Attunement from 4 tiers to 2
- Explicitly resolving Rejuvenation's war cancellation
- Addressing the Arcane Saturation reset exploit
- Moving the Mastery Path deadline later
- Providing tech and hero integration maps

Do those things, and this is a significantly better magic system than the current one. Skip them, and the redesign risks replacing one set of complaints ("it's opaque and unfair") with another ("it's overcomplicated and I don't know what anything does").

The vision — magic as preparation, information, and sustained pressure rather than a parallel arms race — is correct. The implementation needs editing, not reinvention.
