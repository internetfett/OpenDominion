# Magic System — Critical Design Issues

---

## 1. The Ratio Arms Race Is Unwinnable for Attackers

The wizard ratio determines both offensive success and defensive resistance simultaneously. A dominion that invests more in wizards becomes harder to hit *and* more effective at hitting others — offense and defense are fed by the same resource with no counterplay asymmetry.

**Attackers can never out-ratio explorers.** Attackers must split their population between offensive units, defensive units, and wizards. Explorers only need defense and wizards — no population is wasted on offense. This fundamental population difference means explorers will always have more peasants, more platinum, and therefore the ability to train more wizards than an equivalently-sized attacker. When an attacker tries to "invest in wizards" to compete, the magic-focused explorer can simply afford to train even more, maintaining or widening the gap.

This creates a self-reinforcing dynamic: the rich get richer magically, and catching up through investment is nearly impossible without abandoning military viability. Mastery compounds the problem further — it accumulates only through successful offensive casts, rewarding those already winning.

**This problem cascades across all three competitive systems.** The same population advantage that lets explorers dominate wizard ratio also lets them dominate spy ratio — and the cross-system counters all require winning the system you're already losing. Assassinate Wizards requires winning the spy ratio race; Disband Spies requires winning the wizard ratio race; Magic Snare is gated behind war ops requiring military action. Worse, a magic-dominant explorer can cast Disband Spies to degrade the enemy's spy ratio, making their own spy operations more effective too. Winning one ratio cascades into winning the other, compounding into information dominance, better military targeting, and total system control from a single population advantage.

---

## 2. War Spell Damage Has Inconsistent Counterplay

**Fireball's core balance problem is exponential.** Population growth is percentage-based — peasants recover as a proportion of current peasant count. This means each successive Fireball doesn't just kill peasants, it reduces the *recovery rate* of all future growth. The economic damage compounds: a player knocked to 30% peasant population doesn't recover in the same time as one knocked to 60%, they recover in dramatically longer because every tick's growth is calculated from the diminished base. This made Fireball extremely difficult to balance — without protection it could spiral a target into an unrecoverable economic hole, but the current fix (automatically protecting 50% of peasants) leaves some players feeling the spell no longer makes a meaningful impact. The design is caught between "game-ending" and "irrelevant" with little middle ground.

**Lightning Bolt has no equivalent mechanic.** It still deals its full damage with no protection mechanism beyond the ratio-based success rate that attackers structurally cannot win. This is an inconsistency: one war spell has been given ratio-independent counterplay while the other remains entirely gated by the unwinnable ratio competition.

Burning and Lightning Storm further compound this — once the first war spell lands, subsequent casts deal escalating damage. The defender takes increasing punishment with no ability to interrupt the chain. The optimal play is to spam the same spell repeatedly with increasing returns.

---

## 3. Hostile Spell Debuffs Are Non-Interactive

Once Plague, Insect Swarm, Earthquake, or Great Flood lands, the defender waits 8+ hours with no recourse. There is no dispel, no counter-cast, no allied assistance. The only response is to prevent the spell from landing in the first place — which loops back to the ratio arms race that attackers cannot win.

Hostile spells are also too marginal in impact to justify investment. 8-hour debuffs to food, gem, or boat production create small inconveniences that rarely influence game outcomes. Players correctly perceive that military pressure is more effective per resource spent, so hostile spells become irrelevant — they don't hurt enough to matter, but when they do land, they offer zero counterplay.

---

## 4. Team Magic Is Gatekept by Realm Roles

Friendly spells are restricted to the Grand Magister and Court Mage roles. Most players — even skilled, mana-rich wizard specialists — cannot cast beneficial spells on realmmates. A realm of 12 members might have 10 players watching a teammate get magically hammered with no ability to help through the magic system.

This creates single points of failure: if the Grand Magister goes inactive or gets destroyed, the entire realm loses access to Arcane Ward and Spell Reflect for members in need. One player's absence removes a defensive dimension from the team.

---

## 5. Spell Reflect Is Exploitable

Spell Reflect's 3-hour duration is easily scouted via Revelation and waited out. Even when active, its single-use-per-cast design is exploitable — an attacker can probe with a cheap hostile spell to consume the reflect, then immediately follow with a war spell before it can be recast. The defense is weaker than it appears and feels performative rather than genuinely protective.

---

## 6. Being Snared Is Pure Passive Punishment

When wizard strength falls below the casting threshold, the player has zero agency — no ally can help, no item accelerates recovery, no emergency mechanic exists. The experience is sitting and watching the game for several ticks. Resilience (accelerated recovery when snared) is good design intent but counterintuitive: the penalty state secretly contains a recovery mechanism that players won't know about unless they read the docs.

---

## 7. Late-Round Magic Falls Off

Military power compounds through elite units, tech, and land growth. Magic power grows only through tech and wonders — there is no equivalent scaling. Magic-focused builds lose relative power during the round's decisive phase, exactly when they should matter most.

---

## 8. Realm Magic Strength Is Luck of the Draw

Realms don't control which playstyles their members choose. A realm that happens to have more magic-focused players has a structural advantage in the spell war — more casters to rotate Arcane Ward and Spell Reflect (if given roles), more wizards to sustain offensive spell campaigns, and more mana production to keep pressure up. The opposing realm can't counter this by "choosing to train more wizards" — individual players pick their race and strategy independently, and realm composition is largely determined at assignment time. This imbalance is part of why friendly spells were restricted to designated realm roles in the first place: without the role gate, realms with more magic players would dominate even harder. But the role restriction created its own problems (sections 4 and 5), and the underlying issue remains — magic's team impact scales with how many players happen to invest in it, which no realm can reliably control.

---

## Priority Summary

| Priority | Issue |
|---|---|
| Critical | Ratio arms race is structurally unwinnable for attackers — explorers always have more population to invest (#1) |
| Critical | Lightning Bolt has no counterplay mechanic equivalent to Fireball's peasant protection (#2) |
| Critical | No dispel — once debuffed, defenders are passive until expiry (#3) |
| Critical | Friendly spells restricted to 2 realm roles — most players cannot contribute to team defense (#4) |
| High | Burning/Lightning Storm snowball dynamics make war spell chains feel hopeless (#2) |
| High | Spell Reflect is trivially scouted and probed (#5) |
| Medium | Being snared offers zero agency (#6) |
| Medium | Late-round magic scaling falls behind military (#7) |
