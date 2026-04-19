# Magic System Redesign

## Problem Statement

The current magic system has one lever: wizard-to-land ratio. More wizards means higher spell success rates and more damage. This produces three cascading failures:

1. **No skill expression.** A "good" magic player and a "bad" magic player make identical decisions. The only variable is how many wizards were trained at dominion creation. There are no in-game choices that separate them.

2. **Economy-strangling meta.** The only way to improve magical output is to trade economy (land, buildings, military draftees) for wizards. The optimal mage sacrifices the most — a failure state for competitive design.

3. **Defenders are structurally helpless.** Magical defense and magical offense are derived from the same pool (wizard ratio). A defender cannot invest specifically in magical defense without also improving their offense — and they face attackers who have nothing else to spend on. Attackers, already shrunk by sending troops out, can still dominate magically if they pre-loaded wizards. The defender who just repelled an invasion cannot out-wizard someone who committed entirely to wizards.

The redesign separates magical capability into multiple independent dimensions, introduces in-game decisions that create genuine skill expression, and gives defenders real structural tools that do not require matching the attacker's wizard investment.

---

## Design Principles

- **Defenders invest in buildings; attackers invest in wizards.** These two paths coexist and check each other.
- **In-game decisions matter.** The best mage should win through timing, sequencing, and resource management — not just army composition.
- **Mana stockpiles are not waste.** A high mana reserve should provide passive defensive value, so a conservative player is not automatically weaker.
- **Skill is legible.** Players should be able to see why they won or lost a magical exchange, not just attribute it to ratio disparities.

---

## System 1: Split Magical Capacity

### Current State

One wizard ratio governs both offensive spell success and resistance to incoming spells. The only differentiation is through race perks and Spires investment, both of which still reduce to "your ratio number."

### Redesign

Split magical capability into two independent scores:

**Arcane Power (AP)** — Offensive casting strength.
- Derived from: `(Wizards + Archmages×2 + Partial Wizard Units) / Land × multipliers`
- Multipliers: Spires castle improvement, offensive race perks, Sorcerer hero, tech perks.
- Governs: Hostile spell success rates, war spell damage output.
- This is essentially the existing wizard ratio, renamed and isolated to offense.

**Arcane Resilience (AR)** — Resistance to incoming spells.
- Derived from buildings and infrastructure, **not units**.
- Formula: `(Wizard Guilds×1.5 + Towers×0.75 + Masonry×0.5) / Land × multipliers`
- Multipliers: Defensive race perks, Walls castle improvement, Energy Mirror spell (×1.5 while active), tech perks.
- Governs: Incoming hostile spell success chance reduction, incoming war spell damage reduction.
- AR is a separate axis from AP. Training more wizards does not increase AR.

### Impact

A heavily militarized dominion that invested in Wizard Guilds and Masonry has meaningful magical defense even with zero wizards. An all-wizard attacker faces a target whose buildings hit back. A defender does not need to out-wizard their attackers — they need to out-build them.

This resolves the structural asymmetry: attackers invest in people (wizards); defenders invest in buildings (Guilds, Masonry, Walls). The arms race branches, and neither branch dominates the other.

---

## System 2: Mana Shield

### Current State

Mana is strictly a casting fuel. A large mana reserve is wasteful (2% decay per tick). Conservative players are punished for not spending mana.

### Redesign

Large mana reserves provide passive magical protection.

**Mana Shield:**
- Each tick, the dominion's current mana stockpile generates a Mana Shield equal to **2% of the stockpile**.
- Incoming war spell damage first depletes the Mana Shield before affecting the dominion.
- The Shield replenishes fully at the start of each new tick.
- Mana consumed by absorbing damage does **not** count as spell casting (no Wizard Strength cost, no cooldown).
- **Hard cap:** Mana Shield can absorb at most 35% of a single incoming war spell's damage. It does not make a dominion immune.
- **Interaction with decay:** The 2% per-tick decay still applies. The Mana Shield is a passive use of the reserve; it does not prevent decay.

### Impact

A defender who is not actively casting (and therefore has a full or growing mana reserve) has a natural passive defense advantage. An aggressive caster who drains their pool loses this protection. This creates an immediate tension that did not exist before:

- Attacking a mana-rich target is harder. Time your strikes to when they've been forced to spend.
- Defenders with ample Towers who are not casting are inherently harder to damage than their wizard ratio suggests.
- The "optimal mage" is no longer someone who drains every drop of mana every tick. Judgment about when to hold matters.

---

## System 3: Brace

### Current State

Defenders have no reactive options. When they know a magical attack is coming, they can do nothing but hope their ratio holds.

### Redesign

Introduce **Brace** — a new defensive magic action.

**Brace:**
- Available once every 12 hours per dominion.
- Cost: 20 Wizard Strength.
- Effect: For the next 6 hours, reduce all incoming hostile spell success chance by 25% and all incoming war spell damage by 20%.
- Stacks additively with Energy Mirror, Arcane Ward, and Mana Shield (all subject to the existing 80% damage reduction hard cap).
- Does not require success roll. Always applies.
- Visible to opponents who cast Revelation (shows as an active effect).

### Impact

A defender who sees a magical attack building can now respond. They spend Wizard Strength — the same resource attackers need to cast — to harden their position. This creates real counterplay:

- Attackers want to catch defenders before they Brace.
- Defenders who read the situation correctly can negate a significant portion of an incoming assault.
- Brace is more valuable to defenders (who have spare Wizard Strength from not attacking) than to attackers (who need their Strength for casts). This is structurally intended.
- An attacking dominion whose troops are out (lower Wizard Strength recovery from lower population) can still Brace without committing to training wizards.

---

## System 4: Spell Sequencing and Combos

### Current State

All spells are independent. The order of casting has no effect. There is no reason to plan sequences, predict an opponent's next cast, or respond to an ongoing magical exchange with anything other than casting the same spells again.

### Redesign

Certain spells amplify each other when cast in sequence. Knowing these relationships and executing them correctly is a primary source of skill expression.

**Earthquake → Lightning Bolt**
- While Earthquake is active on a target, Masonry protection is reduced (rubble disrupts castle defenses).
- Lightning Bolt cast against a target with active Earthquake deals **+30% damage**.
- Intent: The skilled mage casts Earthquake, waits for it to register, then follows with Lightning Bolt while the window is open. The defender can deny this by stripping Earthquake with Spell Reflect before the follow-up lands.

**Insect Swarm → Fireball**
- While Insect Swarm is active, the target's food reserves are suppressed, leaving less buffer for their population.
- Fireball cast against a target with active Insect Swarm deals **+20% damage to peasants**.
- Intent: Sustained magical pressure (maintaining Insect Swarm) enables a more devastating Fireball. A defender who breaks the Insect Swarm breaks the combo.

**Plague → Fireball**
- While Plague is active, the target's population is weakened and growth-suppressed.
- Fireball cast against a Plague target deals **+15% damage to peasants**.
- Intent: A slower setup combo (Plague is harder to land than Insect Swarm) with a bigger payoff.

**Amplify Magic → War Spell (new variant)**
- Amplify Magic currently only amplifies the next self spell. It gains a second activation mode.
- If Amplify Magic is consumed to enhance a **war spell** (instead of a self spell): the mana cost doubles and the war spell deals **+40% damage**.
- This is chosen at the moment of casting the follow-up spell, not at Amplify Magic cast time.
- Intent: A high-skill, high-cost opener for burst damage windows. Valuable for Fireball during Burning status or Lightning Bolt during Lightning Storm. Costs significant mana and Wizard Strength coordination.

### Combo Denial

- **Spell Reflect** (friendly spell) can strip an active setup spell (Earthquake, Insect Swarm, Plague) off a realmmate, breaking the combo chain.
- **Brace** reduces the follow-up damage but does not strip the setup.
- **Energy Mirror** reduces the follow-up damage via its standard damage reduction.
- A defender who successfully denies a combo window should feel that they outplayed the attacker. A defender who failed to respond should understand why they took extra damage.

---

## System 5: Wizard Mastery as a Spendable Resource

### Current State

Wizard Mastery is a passive accumulating score that provides small bonuses to mana cost reduction and Wizard Strength recovery. It is invisible to decision-making during play. There are no choices involving it.

### Redesign

Wizard Mastery becomes a **spendable resource** with meaningful in-game uses. Passive bonuses are reduced to make room for active spending.

**Mastery Spending Options:**

| Expenditure | Mastery Cost | Effect |
|---|---|---|
| Empower Spell | 10 | Next cast costs 50% less Wizard Strength |
| Arcane Surge | 20 | Next war spell bypasses Mana Shield entirely |
| Intuition | 15 | Instantly learn target's current Wizard Strength and AR without casting an info spell |
| Efficient Channel | 5 | Next self spell costs 75% of normal mana |

**Passive bonuses (reduced from current):**
- Small mana cost reduction (still present, reduced coefficient).
- Wizard Strength recovery +1 at maximum mastery (down from +2).

**Accumulation:** Unchanged — earned through successful offensive spell casts.

### Impact

Mastery is now a strategic reserve. Good players decide when to spend it: save Arcane Surge for the decisive strike against a mana-rich target; use Intuition before committing to an expensive operation to check if the target is braced. Bad players spend it reactively or not at all.

A dominant magic player who has been successfully casting has a large Mastery pool — an additional advantage that compounds their position. A struggling player who has failed frequently has nothing to spend. This creates a legible power state beyond just "do they have more wizards."

---

## System 6: Attunement — Sustained Pressure Rewards

### Current State

Hostile spells (Plague, Insect Swarm, Earthquake, Great Flood) are stateless. Apply them once, reapply when they expire. There is no memory of whether they have been maintained or for how long. Consistent pressure provides no advantage over occasional pressure.

### Redesign

Introduce **Attunement Stacks** on hostile spells.

**Attunement Stacks:**
- When a hostile spell is successfully applied to a target, the caster gains 1 Attunement Stack for that spell against that target.
- Maximum 3 Attunement Stacks per spell per target.
- Each stack increases the spell's effectiveness (debuff magnitude) by **+15%** (additive).
  - Example: Plague at 3 stacks applies a population growth debuff that is 45% stronger than the base spell.
- Stacks persist as long as the spell is maintained. If the spell expires before being recast, all stacks for that spell are lost.
- **Defender countermeasures:**
  - Spell Reflect clears all Attunement Stacks from the next hostile spell it reflects.
  - Arcane Ward on the target prevents new stacks from forming for its duration (but does not strip existing stacks).

### Impact

Sustained magical pressure has a compounding reward. A mage who has consistently maintained Plague for three recast cycles is applying a meaningfully stronger debuff than one who just landed it. This makes long-term magical engagements strategically interesting — and gives defenders a specific, visible reason to break pressure via Arcane Ward or Spell Reflect rather than passively absorbing debuffs.

---

## System 7: Arcane Doctrine

### Current State

All magic players are functionally identical. Their ratio number may differ, but the mechanics they interact with are exactly the same. There is no way to specialize, and therefore no way to have a "style" of magic play.

### Redesign

Introduce **Arcane Doctrine** — a specialization chosen once per round (at round start, or during a brief early-round window) that changes how a dominion's magic works.

Doctrines are not races — they are overlaid on top of race abilities. Every race can adopt any doctrine.

**Doctrine: Warmage**
> Specializes in direct, destructive magic at the cost of stamina and sustainability.
- War spell damage: +25%
- Hostile spell duration: −25%
- Wizard Strength recovery: −10%
- *Flavor:* Burns hot and fast. Effective in short, decisive engagements but cannot sustain prolonged campaigns.

**Doctrine: Hexmaster**
> Specializes in debilitation and control magic.
- Hostile spell success rate: +10%
- Hostile spell duration: +25%
- Attunement Stack cap raised to 4 (instead of 3)
- War spell damage: −20%
- *Flavor:* A slow, methodical grind. Controls the engagement timeline. Difficult to ignore over a long war.

**Doctrine: Wardcaster**
> Specializes in magical defense for self and realm.
- Arcane Resilience: +50%
- Brace effect: +50% stronger (37.5% hostile success reduction, 30% war damage reduction)
- Can cast friendly spells without holding a realm role
- Offensive AP: −20%
- *Flavor:* The team's magic anchor. Protects realmmates and absorbs punishment. Not a threat but not a target worth attacking either.

**Doctrine: Runesmith**
> Specializes in economic self-buff magic at the expense of offensive capability.
- Self spell mana cost: −40%
- Self spell duration: +30%
- Mana production: +15%
- Cannot cast War Spells (Fireball, Lightning Bolt, Cyclone)
- *Flavor:* Trades all offensive threat for economic efficiency. Maintains self-buffs almost for free. Extremely annoying to debuff because they can maintain counter-buffs indefinitely.

**Doctrine: Arcanist** (default)
> No bonuses or penalties. Full access to all spells.
- *Flavor:* The generalist. No edge in any dimension, but also no weakness. Appropriate when strategic flexibility is more valuable than specialization.

### Impact

A Wardcaster with a medium wizard ratio now meaningfully resists a Warmage with a high ratio, because their AR bonus and Brace amplification create structural defenses the attacker must overcome. A Hexmaster who casts war spells instead of hostile spells is visibly underperforming — their doctrine penalizes war spells and their advantage (Attunement Stacks, duration) goes unused. This makes "good mage vs bad mage" visible in play, not just in army size.

---

## System 8: War Footing Protection

### Current State

An attacking dominion whose troops are deployed (home timer running) has lower population, lower resource production, and no special magical protections. If an opponent mounts a magical offensive during this window, the attacker cannot meaningfully respond. This discourages offensive military play in the presence of strong enemy mages.

### Redesign

Introduce **War Footing** — a passive state applied automatically while a dominion's troops are out on an invasion.

**War Footing:**
- Automatically active while any troops are in-flight (home timer > 0).
- AR bonus: +30% (troops out increases defensive vigilance of those remaining).
- Brace effect: +50% stronger while War Footing is active.
- Does not affect offensive AP.
- Expires when troops return home.

**Proportional Defense (always active, not War Footing-specific):**
- Dominions below 75% of the round's average land size receive a passive AR bonus: `(1 - size_fraction) × 20%`.
- Example: A dominion at 50% of average land receives a +10% AR bonus.
- This prevents pile-on scenarios where multiple large casters hammer a small opponent into oblivion.
- Caps at +20% AR (for very small dominions).

### Impact

An attacker who commits to an invasion is not a sitting duck for magical retaliation. They gave up troops — they should not also be stripped of all magical defense. War Footing incentivizes bold military play by removing the "I'll just wait for you to invade someone and Fireball you" counterplay that currently punishes aggression.

---

## System 9: Arcane Forecast (Intelligence Reform)

### Current State

The only way to know what magic an opponent is planning is to cast Revelation (reveals active spells) or Disclosure (reveals heroes). Neither reveals intent or preparation state. Defenders react to damage already done, not to threats developing.

### Redesign

Add **Arcane Forecast** — a lightweight information action.

**Arcane Forecast:**
- Cost: 3 mana × land (very cheap, approximately one-fifth the cost of a typical hostile spell).
- Always succeeds (no roll, no Wizard Strength cost).
- Reveals: Whether the target currently has any **Attunement Stacks** accumulating against your dominion, and whether the target's Wizard Strength is above or below 70.
- Does **not** reveal: Spell identity, stack count, or exact Wizard Strength value.
- Usable during war without restriction.

**Spell Preparation (optional pre-cast):**
- Before casting a war spell, a caster may optionally spend 2 ticks (2 hours) "charging" the spell.
- Charging cost: 50% of the spell's mana cost paid over the 2-tick window.
- After completion: the next cast of that specific war spell within 6 hours deals **+35% damage** and costs 25% less Wizard Strength.
- **Preparation is visible to Arcane Forecast** — a target who casts Arcane Forecast on the preparing caster will learn that a war spell is being prepared (but not which one).
- This creates a deliberate information tradeoff: more damage, but your target gets a warning window to Brace or cast Spell Reflect.

### Impact

Defenders now have a cheap, accessible way to sense incoming magical threats. Attackers choose between the maximum-damage Preparation path (which telegraphs) and the immediate-cast path (which does not). This is a meaningful tactical decision. A skilled attacker might prepare against a distracted target and cancel if the target starts Bracing. A skilled defender checks Arcane Forecast regularly and responds to threats rather than absorbing damage passively.

---

## How This Resolves the Core Problems

### Problem: No difference between good and bad magic players.

**Resolution:** Spell sequencing combos, Mastery spending decisions, Brace timing, Arcane Forecast + Preparation windows, and Attunement maintenance all create decisions where correct play produces better outcomes than incorrect play. A Warmage who blindly fires Lightning Bolts without first landing Earthquake is leaving 30% damage on the table. A defender who never Braces is taking 20–37.5% more incoming damage than they should. These gaps are visible and learnable.

### Problem: Best mages are the ones who stifle their own economy the most.

**Resolution:** Arcane Resilience is building-based. Mana Shield rewards holding mana reserves. Wardcaster and Runesmith doctrines provide magical capability through different resource axes. A player does not need to flood their population with wizards to be a dangerous or resilient magical actor. The meta expands beyond "train as many wizards as possible."

### Problem: Defenders cannot keep up, especially attackers with low population.

**Resolution:** AR scales with buildings (which do not leave during invasions). War Footing Protection specifically buffs the defender-during-attack state. Proportional Defense protects small dominions from being magically overwhelmed. Brace gives any dominion (regardless of wizard ratio) a meaningful reactive tool. The combination means a good defender with appropriate building investment can survive sustained magical assault even against a ratio-superior attacker.

---

## Interactions and Backward Compatibility

### What does not change

- Wizard Ratio (now AP) still governs offensive spell success and war spell damage base.
- Wizard Strength works identically (stamina pool, snare mechanic, Resilience recovery).
- Mana production, cost scaling, and 2% decay are unchanged.
- All existing spells (including racial spells) work identically.
- Chaos League chaos/critical failure mechanics are unchanged.
- Friendly spell roles (Grand Magister, Court Mage) are unchanged.
- Existing tech perks, wonder bonuses, and hero perks apply to AP or AR as appropriate based on their existing flavor (offensive wizard power → AP, defensive wizard power → AR).

### AR Initialization

Existing tech perks and wonder bonuses described as "defensive wizard power" map directly to AR multipliers. Race perks that currently split between "offensive wizard power" and "defensive wizard power" now explicitly map to AP and AR respectively.

Masonry's existing Lightning Bolt protection is folded into its AR contribution. Wizard Guild's existing Fireball protection is folded into its AR contribution. The per-building numbers may need tuning to ensure the new AR formula produces balanced outcomes relative to existing ratios.

### Doctrine Selection

Doctrine is selected at round start in the dominion creation screen alongside race, hero class, and alignment. Arcanist is the default and requires no active choice. A brief (first 24 hours of round) reselection window is available in case of error.

---

## Balance Notes and Design Risks

**Risk: AR too strong, attackers can never succeed.**
Mitigation: AR reduces incoming damage and success chance but cannot block spells entirely (1% success floor still applies). The Arcane Surge Mastery spend bypasses Mana Shield. Warmage doctrine's +25% war damage offsets AR for focused casters. Combo damage bonuses (Earthquake → Lightning Bolt) also increase attacker output against AR-heavy targets.

**Risk: Combo mechanics too complex for new players.**
Mitigation: Combos are additive bonuses, not requirements. A player who casts all spells independently still plays a valid game. Combo benefits are visible in the spell description ("This spell deals +30% damage against targets with active Earthquake"). Discovery is organic.

**Risk: Doctrine system reduces flexibility, frustrating players mid-round.**
Mitigation: The 24-hour reselection window handles errors. Arcanist is always available as a no-commitment default. The doctrines are polarizing by design to create distinct identities, but none locks out a player from strategic adaptation — even Warmage can still cast hostile spells (at reduced duration).

**Risk: Attunement Stacks create runaway pressure scenarios.**
Mitigation: Arcane Ward prevents new stacks from forming. Spell Reflect strips stacks on reflection. The 3-stack cap limits the maximum multiplier to 1.45× (base + 45%). Stacks are lost completely on spell expiry, so any lapse in pressure resets the advantage.

**Risk: Brace and War Footing together make attacking too safe.**
Mitigation: War Footing only applies while troops are out (a time-limited window). Brace has a 12-hour cooldown, meaning it cannot be permanently maintained. Neither applies to offensive AP — the attacker is still magically weaker on offense while Braced or War Footing.
