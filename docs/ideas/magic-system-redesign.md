# The Arcane Confluence — A Magic System Redesign for OpenDominion

---

## Design Vision

The current magic system is, at its core, a second arms race layered on top of the military arms race. The player with more wizards hits harder and resists better, creating a deterministic feedback loop where leading players compound their magical advantage the same way they compound their military advantage. Defensive responses are purely passive. Most players are locked out of supporting their realm through magic. And the spells themselves — especially hostile ones — rarely feel consequential enough to matter.

This redesign starts from a different premise:

**Magic should reward different skills than military.** Where military rewards scale, timing, and coordination, magic should reward information, patience, specialization, and the ability to respond intelligently to pressure. A small, magic-focused dominion should be a genuine threat to a military giant — not through a parallel arms race, but through fundamentally different tools.

**Defense should be a decision, not a waiting room.** Every hostile spell that lands should create an active choice for the defender. The question should never be "how long until this debuff wears off?" but "what do I do about this right now?"

**The whole realm should feel magical.** Not just two role-holders. Every player who invests in magic should be able to contribute to their realm's magical posture in a meaningful way.

**Magic should create drama.** Moments where a war mage channels a devastating strike, where a seer's intelligence prevents a catastrophic invasion, where a warden's counterspell turns an assault back on the attacker — these are the memories players talk about after a round ends.

---

## The Five Design Pillars

1. **Offense and defense are separate investments** — you cannot maximize both simultaneously, and being strong in one does not automatically make you strong in the other.
2. **Failure is exhausting, not fatal** — you pay in stamina, not in permanent unit loss.
3. **Every incoming spell creates an active decision** — there is no passive state for a player under magical attack.
4. **Every realm member can contribute to magical defense** — not just the two people who got the right titles.
5. **Specialization creates genuine identity** — a War Mage and a Seer should feel like completely different playstyles, not just different numbers in the same ratio.

---

## Core Concepts (Replacing the Current Vocabulary)

The following terms replace or significantly redefine the current system's core concepts. The old terms should be deprecated entirely to avoid confusion.

**Spell Power** — The offensive measure of magical capability. Determines the success rate and potency of hostile, war, and info spells cast *by* this dominion. Derived primarily from Wizards and Spires investment.

**Ward Rating** — The defensive measure of magical capability. Determines how well this dominion resists incoming hostile and war spells. Derived primarily from Archmages and Wizard Guilds.

**Mana Reservoir** — Individual mana pool, produced by Towers. Has a maximum capacity. No decay. When full, surplus converts to Surge Charges.

**Surge Charge** — Stored magical potential (max 5). Spent to Empower a spell, dramatically amplifying its effect. The "save for the right moment" resource.

**Arcane Overload** — A temporary debuff applied to the caster when offensive spells fail. Reduces Spell Power for a period. Recovers passively. Replaces permanent wizard death.

**School Attunement** — A dominion's affinity for each of the four magical schools, determined by race, buildings, and tech. Higher attunement means better results when casting within that school.

**Blight Stack** — A stackable negative effect from School of Ruin spells. Stacks up to 3, decays 1 per tick.

**Counterspell Window** — A 4-hour window that opens on the target when a hostile or war spell successfully lands. During this window, the target can actively respond.

**Mastery Path** — A one-time choice made before competitive play begins (by Day 7) that defines the player's magical identity for the entire round. Unlocks unique spells and a passive realm aura.

**Hostile Acts** — Actions that grant temporary access to war spells against a specific target without requiring a realm-level war declaration.

---

## System 1: The Mana Reservoir and Surge Charges

### The Problem Being Solved

Mana decay at 2% per tick forces constant low-level casting as the only rational play pattern. It punishes irregular playtime, punishes strategic restraint, and eliminates "saving up for a big moment" as a valid choice.

### The New System

Mana is produced by Towers (unchanged) but no longer decays. Instead, the reservoir has a hard maximum capacity: `capacity = Tower count × base production × 12`. This means 12 ticks of production can be stored — roughly half a day of mana at maximum Tower investment.

When the reservoir is at maximum capacity, any mana that would be produced instead generates a **Surge Charge**, up to a maximum of 5 stored Surge Charges. Surge Charges do not decay.

**Spending Mana:** Spells cost mana as normal (scaled by land size). Regularly casting empties the reservoir, making room for continued production.

**Spending Surge Charges:** When casting any spell, the player may choose to spend 1 Surge Charge to **Empower** it. Empowered spells have enhanced effects:
- Self-buff spells: +75% duration instead of base duration
- Hostile spells: +1 Blight Stack applied on success (see System 4)
- War spells: +25% damage and ignores 25% of the target's Ward Rating
- Info spells: Generates a Persistent Ward instead of a snapshot (see System 6)

### Strategic Consequences

A player who casts constantly will have an empty reservoir and no Surge Charges — high volume, low impact. A player who paces their casting, letting the reservoir fill, will accumulate Surge Charges that can be deployed at decisive moments — lower volume, high impact when it matters.

This creates two valid play styles: the pressure wizard (constant harassment building Blight Stacks) and the burst wizard (patient buildup, devastating empowered strikes at critical moments). Both are effective; both create different experiences.

A player going to sleep with a full reservoir does not waste production — they wake up with 5 Surge Charges. Being offline is no longer a magical tax.

---

## System 2: Spell Power and Ward Rating

### The Problem Being Solved

Wizard ratio drives both offense and defense from the same investment pool. Leaders compound magical advantage; losers cannot contest. You cannot be a specialist in one without being good at both.

### The New System

Magical capability is split into two completely independent statistics:

**Spell Power** is built from:
- Wizards (1 SP per wizard / total land)
- Spires castle improvement (bonus to SP specifically)
- Offensive SP tech perks
- Offensive SP race perks
- Mastery Path bonuses (War Mage, Hexblade)

**Ward Rating** is built from:
- Archmages (1.5 WR per archmage / total land)
- Wizard Guilds (bonus to WR, scales with building %)
- Defensive WR tech perks
- Defensive WR race perks
- Mastery Path bonuses (Warden)

The offensive spell success formula uses YOUR Spell Power versus the TARGET's Ward Rating. These are now always different stats, drawn from different investments.

### Archmages Redesigned

Archmages no longer simply count as "2 wizards." They are now the dedicated defensive unit:
- Archmages contribute only to Ward Rating, not Spell Power
- Their training cost is adjusted to be slightly less than current (they no longer contribute offensively, so they need to be compelling purely as defenders)
- Races with "immortal wizards" perk now have this apply to Archmages specifically (their defensive unit) rather than Wizards

### Consequences

A "glass cannon" build: high Wizards/Spires investment, few Archmages, minimal Wizard Guilds. Devastating offensively. Vulnerable to incoming spells. A legitimate, interesting archetype.

A "ward fortress" build: few Wizards, heavy Archmage training, high Wizard Guild investment. Resistant to magic. Poor at offensive casting. Also a legitimate archetype.

Mixed builds are still strong — but now you're making genuine trade-offs rather than just "invest more in the one stat."

A militarily strong player who doesn't invest in Archmages/Wizard Guilds has a genuinely exploitable weak Ward Rating. A magic-focused player CAN successfully cast against a military giant by investing heavily enough in Spell Power. The ratio arms race is broken.

---

## System 3: Arcane Overload (Replacing Wizard Death)

### The Problem Being Solved

Wizard losses on failed casts permanently punish players for attempting to contest stronger opponents. It locks underpowered players out of offensive magic entirely and creates a compounding disadvantage spiral.

### The New System

When an offensive spell fails, the caster suffers **Arcane Overload** — a temporary reduction to their Spell Power. Overload severity scales with the ratio mismatch:
- Casting when modestly outmatched: mild Overload (-10% SP for 4 ticks)
- Casting when heavily outmatched: moderate Overload (-25% SP for 6 ticks)
- Casting when catastrophically outmatched: severe Overload (-40% SP for 8 ticks)

Arcane Overload is fully visible on the dominion's status screen with a clear countdown. Multiple failed casts stack Overload, increasing both the penalty and the duration.

Wizards no longer die. The caster's magical capacity is temporarily exhausted, not permanently reduced. A player who swings for a target well above their weight class gets shut down for 8 ticks and needs to wait for recovery — but they still have their wizards, their mana, and their Ward Rating intact.

**Resilience applies to Overload recovery** (exactly as it currently applies to wizard strength recovery when snared). A dominion that has been Overloaded gains Resilience, which accelerates recovery. Getting knocked down magically does not mean staying down forever.

**Arcane Surge and Overload interaction:** Spending a Surge Charge when you are outmatched entirely negates the Overload from that cast — the stored power insulates the caster. This gives players another meaningful reason to save Surge Charges: protection while punching up.

### What Does Not Change

The minimum success floor (~1%) and maximum ceiling (~97–98%) remain. There is still variance. But the penalty for attempting a difficult cast is now time, not permanent resources. The underdog can try. They will be punished temporarily for failing — but they can try.

---

## System 4: The Four Schools of Magic and Attunement

### The Problem Being Solved

The spell list is flat. Casting Fireball and casting Plague require no different investment, no different specialization, no different identity. Every mage has access to everything and is equally (in)effective at all of it.

### The Schools

Spells are organized into four thematic schools. Each dominion has an **Attunement** score for each school, ranging from Novice (0.7× effectiveness) through Adept (1.0×) to Master (1.3×) to Grandmaster (1.6×).

School Attunement is determined by:
- **Race** — each race has a pre-set School affinity profile (see System 9)
- **Buildings** — certain buildings boost specific school attunements:
  - Towers: +Attunement to School of Ruin (the more mana capacity, the more Ruin-focused)
  - Wizard Guilds: +Attunement to School of Warding
  - Spires: +Attunement to School of Devastation
  - Schools: +Attunement to School of the Unseen (knowledge begets knowledge)
- **Tech perks** — tech can increase attunement in specific schools
- **Mastery Path** — grants Grandmaster attunement in one school (see System 7)

You can only achieve Grandmaster attunement through a Mastery Path. Everything else caps at Master.

---

### School of Ruin (Hostile Debuffs)

**Theme:** Entropy, corruption, decay. The patient wizard who grinds down enemies over time.

**Spells:**
- **Plague** — Applies 1 Blight Stack: Population Growth Blight. Each stack reduces population growth by 15% and reduces food consumption efficiency.
- **Insect Swarm** — Applies 1 Blight Stack: Harvest Blight. Each stack reduces food production by 12% and farm output.
- **Earthquake** — Applies 1 Blight Stack: Earth Blight. Each stack reduces ore and gem production by 12%.
- **Tidecurse** (replaces Great Flood) — Applies 1 Blight Stack: Maritime Blight. Each stack reduces Dock output and boat generation by 15%.
- **Wither** *(new)* — Applies 1 Blight Stack: Strength Blight. Each stack reduces the target's Spell Power by 8%. Can stack with other Blight types (counts separately).

**Blight Stack Mechanics:**
- Each School of Ruin spell applies 1 Blight Stack of its type to the target
- A target can have a maximum of 3 stacks of any one type
- Each stack decays by 1 per tick (once per hour)
- To maintain 3 stacks of a single effect, a caster must cast that spell every tick — significant sustained investment
- Blight Stacks of different types stack independently (a target can have 3 Population Blight + 3 Earth Blight simultaneously under sustained pressure from multiple casters)
- Blight Stacks are visible to the target if they have a Divination Ward active (see System 6)

**Attunement Effect on School of Ruin:**
- Novice: Blight Stacks applied decay 1 extra per tick (effectively 2 per tick, making sustained pressure nearly impossible)
- Adept: Standard decay rate (1 per tick)
- Master: Blight Stacks applied by this caster have +1 tick duration before first decay
- Grandmaster: Blight Stacks applied have +2 tick duration AND each successful cast has a 25% chance to apply an extra stack

**Design Note:** This fundamentally changes how hostile spells feel. One cast of Plague does much less than the current version — but a War Mage who has been casting Plague every tick for 3 ticks creates a -45% population growth debuff that is genuinely threatening. Sustained magical pressure becomes a real strategic investment and a real strategic threat, rather than a marginal inconvenience from a single cast.

---

### School of Devastation (War Magic)

**Theme:** Power, destruction, overwhelming force. The war mage who breaks things.

**Spells:**
- **Fireball** — Destroys peasants beyond Wizard Guild protection threshold. Now also deals a small amount of food stockpile damage (unchanged from current, but no longer applies Burning).
- **Lightning Bolt** — Destroys castle improvement investment. Player now *chooses* which improvement type to target (science, keep, forges, or walls). Spires and harbor remain exempt. Masonry protects as before.
- **Cyclone** — Targets wonders as before. Redesigned: deals flat damage regardless of owned/unowned status. No longer doubles damage on unowned wonders (eliminating the perverse incentive to destroy your own realm's wonders). Does deal +30% damage during mutual war.
- **Scorch** *(new)* — Applies **Scorched Earth** status: reduces the target's food production by 20% for 6 hours. Does NOT stack with itself (one Scorched Earth at a time). Cheaper than Fireball, requires a Hostile Act but not active war.

**Hostile Acts (Replacing the War Declaration Requirement):**
War spells in the current system require war declaration or recent invasion — a realm-level political commitment just to access individual magical tools.

In the new system, the caster earns **Hostile Act** credit against a specific target through:
- The caster's realm has invaded the target's dominion in the last 12 hours
- The target's dominion has successfully cast a School of Ruin or Devastation spell on the caster in the last 24 hours (retaliatory access)
- The target has stolen resources from the caster in the last 12 hours
- Active escalated war between realms (unchanged — war still grants access)

Hostile Act credit is *individual* to the caster-target pair, not realm-wide. If an enemy mage hits YOU with Earthquake, YOU gain Hostile Act credit to cast Devastation spells against them, regardless of whether your realm has declared war.

This makes war magic feel earned rather than gated behind political process. Getting hit is now both a problem to solve and a door that opens.

**Attunement Effect on School of Devastation:**
- Novice: -25% damage, +25% mana cost
- Adept: Standard damage
- Master: +15% damage, chance to apply Scorched Earth on any successful Devastation spell
- Grandmaster: +30% damage, Fireball and Lightning Bolt ignore 30% of the target's Ward Rating, and Empowered Devastation spells have a 20% chance of a Critical Strike (×1.5 damage) on top of their standard Empower bonus

**Critical Success Redesign:** Critical success (1.5× damage) only occurs for Grandmaster Devastation casters via the Empower mechanic. It cannot occur randomly for any other caster. This eliminates the uncontrolled variance that makes planning around Devastation spells impossible.

---

### School of the Unseen (Divination)

**Theme:** Knowledge, sight, intelligence. The wizard who knows before acting.

**Spells:**
- **Clear Sight** — When cast normally: reveals a full status snapshot (current behavior). When cast with a Surge Charge (Empowered): creates a **Persistent Ward** on the target.
- **Vision** — When cast normally: reveals tech snapshot. When Empowered: Persistent Ward for tech.
- **Revelation** — When cast normally: reveals active spells snapshot. When Empowered: Persistent Ward for spell status.
- **Disclosure** — When cast normally: reveals heroes snapshot. When Empowered: Persistent Ward for heroes.
- **Omen Reading** *(new)* — Reveals any Blight Stacks currently on the target, their types, their counts, and their source (if the caster's Divination attunement is Master or higher). Standard cast, no Empower required.

**Persistent Wards (replacing the snapshot-only model):**
A Persistent Ward on a target updates its intelligence data every tick for its duration (12 hours). The Op Center shows a live view of that target's status — not a snapshot from hours ago, but current information.

This makes information magic actually worth investing in. A Seer with several Persistent Wards active on high-priority targets provides their realm with live intelligence on enemies: current military composition, active spells, incoming land — information that is actually actionable when an invasion window opens.

Persistent Wards cost significantly more mana than snapshot casts (3× the mana, plus 1 Surge Charge). The Seer Mastery Path reduces this cost substantially.

**Attunement Effect on School of the Unseen:**
- Novice: Info spells produce snapshots only (no Persistent Ward access even with Surge Charge)
- Adept: Standard behavior (Persistent Wards available via Surge Charge)
- Master: Persistent Wards last 18 hours instead of 12; Omen Reading also reveals the source caster
- Grandmaster: Persistent Wards update every 30 minutes instead of hourly; Divination spells also reveal whether the target has a Counterspell Window available and how many Surge Charges they hold

---

### School of Warding (Protective Magic)

**Theme:** Protection, reflection, resistance. The wizard who makes their realm a fortress.

**Standard Warding Spells (Self-Cast — Available to All):**
- **Energy Mirror** — Increases Ward Rating by 20% for 12 hours (self-cast, always succeeds, replaces the current self-spell with a meaningful Ward Rating boost)
- **Mana Veil** — Prevents Surreal Perception from identifying the caster's identity for 12 hours (self-cast, always succeeds; for covert magical operations)
- **Arcane Reserve** — Converts 25% of current mana into 1 Surge Charge (costs no mana beyond what is consumed; allows manual Surge generation if the reservoir hasn't filled naturally)

**Realm Warding Spells (Role-Restricted — Grand Magister / Court Mage only):**
- **Arcane Ward** — Increases target realmmate's Ward Rating by 25% for 6 hours. Cooldown: 8 hours per target.
- **Ley Anchor** *(replaces Illumination)* — Anchors the target realmmate to the realm's Ley lines, providing both +15% Ward Rating AND +15% spy defense for 6 hours. Cooldown: 8 hours per target.
- **Sanctum Shield** *(replaces Spell Reflect)* — Creates a one-use magical barrier on a realmmate that negates the NEXT hostile or war spell entirely, reflecting nothing (no amplification, no backlash). Duration: 6 hours (instead of current 3). Can be refreshed before it is consumed.

**Attunement Effect on School of Warding:**
- Novice: Energy Mirror provides only +10% Ward Rating
- Adept: Standard effects
- Master: Energy Mirror lasts 18 hours; Arcane Ward (when cast by role-holders of this attunement) boosts Ward Rating by 35%
- Grandmaster: All Warding spells cost 30% less mana; Sanctum Shield at this level reflects a weakened version (25% power) of the negated spell back to the caster (the only Reflect in the redesigned system, and it is a one-time proc, not amplified)

**Redesigning Spell Reflect:**
In the current system, Spell Reflect is a 3-hour time bomb that trivially circumvented (just wait and probe). In the redesign, Sanctum Shield lasts 6 hours, cannot be probed (it doesn't reflect the probe — it negates it), and only the Grandmaster Warden gets any reflective component. The reflection is 25% power (not amplified), making it a discouragement, not a devastating punishment. Reflection at full or amplified power is eliminated from the system.

---

## System 5: The Counterspell Window

### The Problem Being Solved

Once a hostile spell lands, the defender waits. There is no counterplay, no decision, no agency. This is the single most "not fun" state in the current system.

### The New System

When any hostile or war spell successfully lands on a dominion, that dominion gains a **Counterspell Window** lasting 4 hours. During this window, they may spend mana to actively respond. The response options scale with the defender's Ward Rating relative to what hit them.

**Response Options:**

| Option | Effect | Mana Cost | Ward Rating Requirement |
|---|---|---|---|
| **Absorb** | Accept the effect, gain mana equal to 20% of the spell's cost | Free | None |
| **Diminish** | Reduce the effect's potency by 40% and duration by 40% | 1.5× the spell's mana cost | None |
| **Dispel** | Remove the effect entirely | 3× the spell's mana cost | Ward Rating ≥ 50% of attacker's Spell Power |
| **Resist** | Reduce the effect by 60% and recover 50% Counterspell cost via Mana | 2× the spell's mana cost | Ward Rating ≥ 75% of attacker's Spell Power |

Only one Counterspell response can be used per incoming spell. If the Counterspell Window expires without action, the spell takes full effect.

**Absorb** is always available: a player who is mana-poor or simply not focused on magic can still make a choice — accept the hit and generate a small mana refund. This is not nothing. A player who Absorbs every hit passively generates mana, slightly rewarding even passive magical play.

**Diminish** requires no ratio check. Any player, regardless of Ward Rating, can pay 1.5× the cost to take a reduced hit. This eliminates the feeling of pure helplessness: even a militarily-focused dominion with minimal magical investment can choose to pay the price and take half the damage.

**Dispel** requires decent Ward Rating — you need a real defensive magic investment to fully negate incoming spells. This is the "fortress" player's reward for building Archmages and Wizard Guilds.

**Resist** requires heavy Ward Rating investment and provides the best return on cost — best for players who built their defensive magic specifically for this purpose.

**Against Blight Stacks specifically:**
Counterspell responses remove 1 Blight Stack per use (Diminish), 2 stacks (Dispel), or all stacks of that type (Resist). Absorb on a Blight Stack still generates mana but does not remove any stacks. This means sustained Ruin pressure requires sustained counter-investment — both sides are actively engaged.

### Notifications

When a hostile spell lands, the target receives a notification immediately: the spell type, the approximate effect, and a countdown showing how long the Counterspell Window remains open. The attacker does not know whether the target responded until the window closes (they see the result in subsequent scouting).

This creates a strategic information gap: the attacker doesn't know if their spell stuck, was diminished, or was dispelled. It rewards investment in Divination (Persistent Wards show active Blight Stacks, revealing whether Counterspells were used).

---

## System 6: The Mastery Path

### The Problem Being Solved

Wizard Mastery is a vague background accumulator with opaque bonuses. Players cannot evaluate their mastery level, cannot plan around it, and rarely feel it. There is no magical identity differentiation between players.

### The New System

By Day 7 of the round (before most protection periods end), each player chooses one of five **Mastery Paths**. This choice is permanent for the round and defines the player's magical identity. Each path provides:
- Grandmaster attunement in one or two schools
- Access to 1-2 unique spells no other path can cast
- A **Realm Aura** — a passive bonus that applies to ALL realm members simultaneously, regardless of whether they hold a role

The Realm Aura is the key design element. It means every magic-focused player contributes something to their realm simply by existing, choosing, and playing within their path. No titles required.

---

### Path 1: The Arcanist (Generalist)

*"Magic is not a hammer — it is a key. Every lock has a different shape."*

**Attunement:** Adept in all four schools (no Grandmaster, but unique breadth)

**Unique Spells:**
- **Ley Shift** — Transfer up to 25% of your current mana to a realmmate. Once per 24 hours. Makes mana a shareable resource for the Arcanist specifically.
- **Arcane Audit** — Reveals the target's School Attunement levels, Mastery Path, and Surge Charge count. Costs 1 Surge Charge.

**Realm Aura (passive, no action required):** All realm members receive a 5% reduction to all spell mana costs. Scales slightly with the Arcanist's Tower count (higher mana production = stronger cost reduction, up to 8%).

**Design Role:** The glue wizard. Not the best at anything but able to fill any role the realm needs. The Ley Shift makes them a mana logistics player — they can fuel a Hexblade who is running dry or replenish a War Mage before a critical strike. This creates genuine team coordination dynamics.

---

### Path 2: The War Mage (Devastation specialist)

*"Subtlety is for people who haven't tried fire."*

**Attunement:** Grandmaster in School of Devastation, Novice in School of Ruin, Adept in School of the Unseen and Warding

**Unique Spells:**
- **Inferno** — A once-per-72-hours mega-Fireball that deals 2× standard Fireball damage, ignores Wizard Guild protection entirely, and applies Scorched Earth status. Extremely expensive (10× standard Fireball mana cost). The defining spell of the archetype.
- **Siege Mark** — Designates a target dominion as Siege Marked for 12 hours. All School of Devastation spells cast by ANY realm member against this target deal +15% damage. Can only mark one target at a time.

**Realm Aura (passive):** When the realm is in active escalated war, all realm members gain +5% offensive military power. The War Mage's dedication to magical warfare inspires the entire realm's fighting spirit.

**Design Role:** The heavy hitter. When a realm wants to break a specific dominion, the War Mage declares Siege Mark and all Devastation casters in the realm focus fire. Inferno is the "nuclear option" — expensive, rare, devastating. Players will remember the round they got hit by Inferno. That's the point.

---

### Path 3: The Hexblade (Ruin specialist)

*"I don't need to defeat you. I just need to make you defeat yourself."*

**Attunement:** Grandmaster in School of Ruin, Novice in School of Devastation, Adept in School of the Unseen and Warding

**Unique Spells:**
- **Withering Pall** — Applies 1 Blight Stack of EVERY active Blight type the target currently has (essentially doubling their current stack count). Requires the target to already have at least one Blight Stack. Costs 2 Surge Charges. The devastating follow-up to sustained Ruin pressure.
- **Hex Chain** — The next School of Ruin spell cast on the target by ANY realm member applies +1 bonus Blight Stack. Duration: until triggered or 12 hours. Sets up coordinated realm Ruin assaults.

**Realm Aura (passive):** All Blight Stacks applied by realm members decay 1 tick later than normal. A single tick of delay means every stack of Plague, Insect Swarm, or Earthquake lasts one hour longer. Over an extended campaign, this meaningfully reduces the recast burden on all Ruin casters in the realm.

**Design Role:** The attrition specialist. The Hexblade is not about spectacular moments — they are about grinding. Three Hexblade players in a realm turning a single target into a stack of 9 different Blight effects is a terrifying coordinated assault. The Withering Pall is the ace up the sleeve: wait for the target to have 3 stacks of Earth Blight, then double it to 6 (capped at 3, but the math matters because it resets the decay clock).

---

### Path 4: The Seer (Divination specialist)

*"The battle is decided before either army moves. The only question is whether you know it."*

**Attunement:** Grandmaster in School of the Unseen, Novice in School of Devastation, Adept in School of Ruin and Warding

**Unique Spells:**
- **Fate Sight** — Performs an Empowered Clear Sight (Persistent Ward) on ALL of a target dominion's realmmates simultaneously with a single cast. Extremely expensive. For 12 hours, the realm has live intelligence on every member of the enemy realm.
- **Reveal Weakness** — Analyzes a target's military composition and reveals exactly how many offensive or defensive power they would field in a hypothetical engagement, including all active spell bonuses. Allows perfect invasion planning. Once-per-target-per-day.

**Realm Aura (passive):** All realm members' Persistent Wards update every 30 minutes instead of hourly. Intelligence gathered by the Seer serves the whole realm.

**Design Role:** The intelligence officer. A realm with a skilled Seer knows when enemies are magically vulnerable (Overloaded, low Ward Rating, mana-depleted), knows which targets have been weakened by Blight Stacks, and knows exactly when a military strike will succeed. The Seer doesn't hurt anyone directly — they make everyone else hurt people better. Fate Sight on an enemy realm's full roster is one of the most powerful intelligence plays in the game.

---

### Path 5: The Warden (Protection specialist)

*"Their spells break on my walls. My realm sleeps soundly because I do not."*

**Attunement:** Grandmaster in School of Warding, Novice in School of Ruin, Adept in School of Devastation and the Unseen

**Unique Spells:**
- **Grand Sanctum** — A Sanctum Shield (full spell negation) cast on all realm members simultaneously. Duration: 3 hours. Costs 3 Surge Charges and significant mana. The "dome" spell — the entire realm is shielded from the next hostile or war spell each. Used before anticipated coordinated magical strikes.
- **Arcane Retribution** — When any realm member's Counterspell Window is open, the Warden may cast this to add Resist access to that member's options, regardless of their Ward Rating. Makes defensive Counterspells accessible to military-focused realmmates who would otherwise only have Absorb/Diminish.

**Realm Aura (passive):** All realm members gain access to the **Diminish** Counterspell response at base (free), even if they would otherwise not have it. More critically: the minimum effectiveness of Diminish for all realm members is increased to 50% reduction (up from 40%). Every player in the realm can push back against magic, and they push back harder.

**Design Role:** The protector. A realm with a Warden feels fundamentally harder to break magically. Every player in the realm has better counterspell access. Grand Sanctum is a devastating tool against coordinated Hexblade or War Mage assaults — the moment they commit their Surge Charges to a big push, the Warden shields the whole realm. The timing cat-and-mouse between aggressive and defensive magical specialists is where the most interesting play happens.

---

## System 7: The Arcane Congress

### The Problem Being Solved

Most players cannot meaningfully contribute to their realm's magical defense or offense. Friendly magic is gated behind two role holders. Team cooperation in magic is nearly nonexistent.

### The New System

Once per 72 hours, the Grand Magister (or any Mastery Path holder) may call an **Arcane Congress**. When a Congress is called:

1. All realm members are notified and have a 4-hour contribution window
2. Members may contribute any amount of mana to the Congress pool
3. When the window closes, the total pool determines what Congress Spells are available
4. The Grand Magister (or caller) selects which Congress Spell to cast from the available options
5. The spell resolves immediately

**Congress Spell Tiers:**

| Mana Contributed | Congress Spell | Effect |
|---|---|---|
| 500+ | **Ley Blessing** | All realm members gain +15% mana production for 24 hours |
| 1,500+ | **Arcane Bulwark** | All realm members gain +20% Ward Rating for 24 hours |
| 3,000+ | **Siege Confluence** | All School of Devastation spells against a designated target deal +30% damage for 12 hours (requires Hostile Acts against that target) |
| 6,000+ | **Grand Warding** | Applies Sanctum Shield AND Arcane Ward to every realm member simultaneously. Duration: 6 hours |
| 10,000+ | **Apocalypse Confluence** | Deals a massive combined Fireball and Lightning Bolt strike on a designated target. Deals damage equivalent to 5× standard casts of each, ignoring 50% of Ward Rating. Requires active mutual war. Once per round. |

**Design Notes:**
- Any realm member can contribute mana — not just role holders
- The decision of which tier to unlock is made by the caller after seeing how much was contributed
- Saving a Congress for an Apocalypse Confluence requires sustained planning, communication, and sacrifice from the whole realm — it is a memorable event, not a routine button
- Lower tier Congresses are routine coordination tools; the higher tiers are strategic events

This makes realm-level magical coordination a real strategic dimension. The Arcanist's Ley Shift can top up a low-mana member before a Congress. The Seer's intelligence determines which target to hit. The War Mage adds Siege Mark before the Siege Confluence. Everyone has a role.

---

## System 8: Racial Attunement

### The Problem Being Solved

Racial spells have wildly asymmetric power levels. Some racial spells (Unholy Ghost) are categorically stronger than others (Erosion). Immortal wizards create a two-tiered experience. Wood Elf must trade one system for another with active self-penalty.

### The New System

Every race has a pre-set **School Attunement Profile** that determines their baseline attunement before buildings and tech. Races that are designed around magic begin with better attunements; military-focused races begin at Novice in most schools.

Racial spells are redesigned as School-aligned, with costs reflecting the attunement bonus they provide:

**Unholy Ghost (Dark Elf/Spirit)** — Reclassified as a School of Ruin effect: applies a special Blight Stack (Draftee Blight) that is specifically "enemy draftees contribute 50% DP instead of 100% DP." Unlike standard stacks, this one does not stack (only 1 Draftee Blight at a time) but lasts for 10 hours. This preserves the spell's power while making it a sustained-investment mechanic rather than a free passive toggle.

**Erosion (Merfolk/Lizardfolk)** — Becomes an active choice rather than an automatic conversion: the caster designates conquered land to be rezoned (50% of conquered acres per cast, not all). Players can selectively apply it to captured Plains or Mountain without converting all their new territory. No more accidental terrain transformation.

**Gaia's Light / Gaia's Shadow (Wood Elf)** — Redesigned: these are no longer mutually penalizing. Gaia's Light gives +WPA; Gaia's Shadow gives +SPA. Neither penalizes the other stat. The trade-off is simply which one you boost, not which one you hurt. The Wood Elf becomes genuinely flexible rather than punishing.

**Death and Decay (Undead)** — Now has two distinct modes at cast time: Passive Mode (applies Blight Stacks to a designated enemy; uses the user's Ruin attunement) and Conversion Mode (internal: accelerates food decay and converts peasants to zombies for economic purposes, with a visual and tooltip warning before confirmation). The player never accidentally casts Conversion Mode.

**Immortal Wizards** — The perk is renamed **Resolute Archmages** and applies specifically to Archmages (the Ward Rating unit). When Archmages would be lost in the current system... they are never lost in the new system (see System 3). So this perk is redesigned: Resolute Archmages means that the race's Archmages generate +50% Ward Rating per unit. Their defenders are harder to overcome magically.

---

## System 9: The Full Spell Taxonomy

### Self-Spells (Available to All, Always Succeed, No Roll)

| Spell | School | Duration | Effect |
|---|---|---|---|
| Gaia's Watch | Warding | 12h | +15% food production |
| Midas Touch | Warding | 12h | +12% platinum production |
| Mining Strength | Warding | 12h | +15% ore production |
| Harmony | Warding | 12h | +20% population growth |
| Ares' Call | Warding | 12h | +10% defensive power |
| Energy Mirror | Warding | 12h | +20% Ward Rating |
| Surreal Perception | Unseen | 12h | Reveals source of all successful ops and spells against you |
| Mana Veil | Warding | 12h | Prevents identity reveal even with Surreal Perception active |
| Arcane Reserve | Warding | Instant | Converts 25% current mana into 1 Surge Charge |
| Fool's Gold | Warding | 10h | Protects platinum AND (with standard tech unlock) ore/lumber/mana from theft |

**Non-Stacking Redesign:** Self-spells of the same bonus type no longer silently overlap. Attempting to cast a self-spell when a stronger version is already active produces a clear UI warning: "This spell's bonus would be overridden by your active [X]. Cast anyway?" The player chooses with full information.

### Hostile Spells — School of Ruin (Require Success Roll)

| Spell | Effect |
|---|---|
| Plague | +1 Population Growth Blight Stack |
| Insect Swarm | +1 Harvest Blight Stack |
| Earthquake | +1 Earth Blight Stack |
| Tidecurse | +1 Maritime Blight Stack |
| Wither | +1 Strength Blight Stack (reduces target's Spell Power by 8% per stack) |
| Disband Spies (redesigned) | No longer a magical spell. Moved to espionage as a War Operation. One cross-system hard counter removed. |

### War Spells — School of Devastation (Require Hostile Acts or War)

| Spell | Effect |
|---|---|
| Fireball | Destroys unprotected peasants and food. Empower: +25% damage |
| Lightning Bolt | Destroys chosen improvement type (player selects target). Empower: ignores 25% WR |
| Scorch | Applies Scorched Earth status (-20% food production, 6h). Cheaper war spell. |
| Cyclone | Damages wonders. +30% during mutual war. No longer doubles on unowned wonders. |
| Inferno | WAR MAGE ONLY. 2× Fireball damage, ignores WG protection, applies Scorched Earth. |

### Info Spells — School of the Unseen (Require Success Roll, Easier Curve)

| Spell | Normal Cast | Empowered (Surge Charge) |
|---|---|---|
| Clear Sight | Status snapshot | Persistent Ward (live status, 12h) |
| Vision | Tech snapshot | Persistent Ward (live tech, 12h) |
| Revelation | Active spells snapshot | Persistent Ward (live spells, 12h) |
| Disclosure | Heroes snapshot | Persistent Ward (live heroes, 12h) |
| Omen Reading | Reveals Blight Stacks on target | Master+ also reveals source caster |

### Friendly Spells (Role-Restricted: Grand Magister / Court Mage)

| Spell | Duration | Effect |
|---|---|---|
| Arcane Ward | 6h | +25% Ward Rating on realmmate (Master: +35%). Cooldown: 8h per target. |
| Ley Anchor | 6h | +15% Ward Rating AND +15% spy defense on realmmate. Cooldown: 8h per target. |
| Sanctum Shield | 6h | Full negation of next hostile/war spell on realmmate (Grandmaster Warden: also reflects 25% power back). |

### Path-Exclusive Spells (Mastery Path Required)

| Spell | Path | Effect |
|---|---|---|
| Ley Shift | Arcanist | Transfer up to 25% of mana to a realmmate. Once per 24h. |
| Arcane Audit | Arcanist | Reveals target's School Attunement, Mastery Path, Surge Charge count. Costs 1 Surge Charge. |
| Inferno | War Mage | Once per 72h mega-Fireball. |
| Siege Mark | War Mage | +15% Devastation damage against marked target for all realm casters, 12h. |
| Withering Pall | Hexblade | Doubles all current Blight Stacks on target. Costs 2 Surge Charges. |
| Hex Chain | Hexblade | Next realm Ruin cast on target applies +1 bonus stack. |
| Fate Sight | Seer | Persistent Ward on ALL of target's realm members simultaneously. |
| Reveal Weakness | Seer | Reveals exact OP or DP of target in a hypothetical engagement. Once per target per day. |
| Grand Sanctum | Warden | Sanctum Shield on all realm members simultaneously. Costs 3 Surge Charges. |
| Arcane Retribution | Warden | Grants a realm member Resist-level Counterspell access regardless of their Ward Rating. |

---

## How This Addresses Each Critical Issue

| Original Issue | Solution in This Redesign |
|---|---|
| Ratio arms race (offense = defense) | Spell Power and Ward Rating are entirely separate investments |
| Wizard death punishes weak players for trying | Arcane Overload is temporary Spell Power reduction, fully recoverable |
| No dispel mechanic | Counterspell Window gives every defender 4 hours of active response options |
| Friendly spells limited to 2 roles | Realm Auras give every Mastery Path player a passive contribution; Congress opens to all |
| Rejuvenation cancels war | Redesigned: Scorched Earth / recovery effects are status-only; war is unaffected |
| Hostile spells too low-impact | Blight Stacks scale with sustained pressure; 3 stacks of Plague is a serious threat |
| Burning/Lightning Storm snowball | Replaced with Scorched Earth (flat, non-amplifying) and no amplification mechanic |
| Immortal wizards create two-tiered play | Redesigned to Resolute Archmages (+50% Ward Rating per Archmage) — comparable benefit, equal risk |
| Mana decay forces constant activity | No decay; reservoir cap + Surge Charge generation rewards both active and strategic play |
| Non-stacking buffs silently waste casts | UI confirmation prompt when a cast would be overridden |
| Spell Reflect trivially circumvented | Sanctum Shield (full negation, not reflection); only Grandmaster Warden gets 25% reflection, non-amplified |
| Wizard strength vs. ratio naming | Renamed entirely: Spell Power / Ward Rating / Arcane Overload |
| War declaration required for war spells | Hostile Acts system: earned individually through in-game actions |
| Info spells produce stale snapshots | Persistent Wards: live intelligence for 12–18 hours at Surge Charge cost |
| Mana cannot be shared with allies | Arcanist's Ley Shift; Arcane Congress pool contribution |
| 3-day restriction creates dead time | Retained (round start protection), but Mastery Path choice fills early round with meaningful decisions |
| Erosion can work against the caster | Player selects which conquered acres to apply Erosion to |
| Wood Elf Gaia spells actively penalize | Redesigned: each grants a bonus without penalizing the other stat |
| Death and Decay traps new players | Explicit mode selection at cast time; Conversion Mode requires deliberate opt-in |
| Cyclone double-damages unowned wonders | Eliminated; war bonus is +30% during mutual war regardless of wonder state |
| Uncommunicated Wizard Mastery | Replaced by Mastery Path: clear bonuses, visible aura, unique spells |
| Single points of failure in friendly spells | Realm Auras are passive and unconditional; Congress requires only mana contribution |

---

## New Strategic Decisions Created

The redesign does not just fix old problems — it creates decision spaces that did not exist before.

**The Mastery Path commitment:** Which magical identity best serves your realm's needs this round? If the realm has a War Mage, does it also need a Hexblade, or would a Seer's intelligence create more total value? These decisions require communication and coordination.

**Surge Charge timing:** Do you spend your Surge Charges now on sustained pressure, or save them for the big Congress moment? Or for an Empowered Inferno? Or to negate Arcane Overload on a risky punch-up?

**Counterspell economics:** Every incoming spell is a mana decision. Dispel costs 3× the attacker's cost — can you afford it? Diminish costs 1.5× — is it worth the reduction? Or do you Absorb and generate mana for a counter-attack? These are fast, interesting decisions made under pressure.

**Spell Power vs. Ward Rating allocation:** For the first time, you genuinely choose whether to be offensively or defensively magical. The trade-off has teeth.

**Congress timing and tier targeting:** Calling a Congress too early wastes the tool for 72 hours. Calling it when you need Tier 5 but only raise Tier 3 mana is a failure of coordination. Calling it at exactly the right moment in a war is exhilarating.

**Hostile Acts as targets of choice:** An enemy who hits your Hexblade with Ruin spells just handed the Hexblade retaliatory Devastation access. Do enemies now think twice before targeting your magic specialists? Does your team decide who is the "designated retaliator" to ensure you always have Hostile Act access?

**The Seer's intelligence value:** When a Seer reveals that three enemy dominions are sitting at 3 Blight Stacks of Earth Blight simultaneously, the realm's military players know exactly when to invade for maximum advantage. Magic and military become genuinely integrated, not parallel.

---

## Summary

The Arcane Confluence is not a patch on the existing system. It is a rebuilding from the question: *what is magic for?*

Magic should be the system that rewards intelligence, patience, teamwork, and timing — the opposite skill profile from military's scale, speed, and aggression. A player who understands the magical game should be able to threaten players who would crush them militarily. A realm with a coordinated magical team should be able to hold off a superior military force through information, protection, and accumulated Blight pressure.

Most importantly: magic should create memorable moments. The Inferno that breaks a war. The Grand Sanctum that absorbs a realm-wide strike. The Seer who called the invasion window twenty minutes before the attacker thought they were ready. The Congress that builds to an Apocalypse Confluence that ends a war in one strike.

These are the stories players tell at the start of the next round.
