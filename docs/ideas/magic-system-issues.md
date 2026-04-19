# Magic System Design Issues — OpenDominion

---

## I. FUNDAMENTAL BALANCE PROBLEMS

**1. Ratio-based success creates a runaway-leader dynamic.**
The caster's wizard ratio determines both offensive casting success *and* defensive resistance simultaneously. A dominion that invests more in wizards becomes harder to hit *and* more effective at hitting others. There is no counterplay asymmetry — offense and defense are fed by the same resource. The rich get richer magically, and catching up is nearly impossible without abandoning military investment.

**2. Wizard losses on failure scale with how outmatched the attacker is.**
Weaker players who *attempt* to contest a stronger wizard pay the heaviest price in casualties. This is a double-punishment: first the spell fails, then you lose substantial wizards. It functionally locks underpowered players out of offensive magic entirely, reinforcing the same gap that caused the failure in the first place.

**3. The 1% success floor lets weak wizards land spells on powerful defenders.**
The floor exists to prevent total lockout, but from the defender's perspective it creates unavoidable random damage. A player with 10× your wizard ratio still has a 1-in-100 chance of succeeding. In a persistent game where spells can be cast repeatedly, this floor is regularly hit, creating outcomes that feel unfair and are impossible to fully mitigate.

**4. The 97–98% ceiling means dominant casters still randomly fail.**
A player who has heavily invested in wizard ratio can still fail, still lose wizards, and still be denied their spell's effect through no strategic error. Random failures at the top end of the curve feel punishing and unrewarding. Players who optimized correctly should be rewarded with consistent results, not dice-roll failures.

**5. Racial spells cost 5× the mana of generic spells with no flexible pricing.**
This creates severe mana pressure for races whose identity relies on their racial spell. A Troll without Regeneration or a Dark Elf without Unholy Ghost is meaningfully weaker, yet the 5× cost may force players to skip their signature ability during mana-constrained periods. The binary nature of this cost (either pay 5× or get nothing) lacks nuance.

**6. Mana decays 2% per tick regardless of player activity.**
This is a real-time tax on absence. Players who log in infrequently necessarily waste mana. It punishes irregular playtime and forces a specific activity cadence — constant low-level casting — over strategic burst casting. A player who wants to "bank" mana for a critical moment is penalized simply for waiting.

**7. Hostile spell durations extend during war, compounding pressure on the losing side.**
When you're already losing a war militarily, the enemy's Plague/Insect Swarm/Earthquake effects last longer against you. The mechanic amplifies disadvantage: those who most need magical recovery get less of it. This design makes wars feel increasingly hopeless rather than creating comeback opportunities.

**8. Fireball and Lightning Bolt have a hard 80% damage reduction cap.**
No matter how many Wizard Guilds or Masonry buildings a player invests in, they cannot prevent more than 80% of war spell damage. A player who builds a fortress of protective structures still gets hit. This cap makes heavy defensive investment feel futile — you can reduce damage, never eliminate it. Players who try to "build out" of the problem discover it's mathematically impossible.

**9. Wizard Guilds serve two completely unrelated functions.**
Wizard Guilds produce mana (magical economy) *and* protect peasants from Fireball (war defense). These are mechanically unrelated purposes crammed into one building type. Players cannot choose "I want mana production but not Fireball protection" or vice versa. Build decisions are forced into a bundle that may not match strategic needs.

**10. Towers produce mana as the primary source, competing with other swamp-land buildings.**
The Tower/Wizard Guild/Temple land competition means that every choice to improve magical capability comes at the direct cost of military capability (Temples reduce enemy DP). This is intended as a trade-off, but in practice it creates severe pressure that forces players into one of two extreme builds: magic-heavy or military-heavy. Mixed builds underperform both.

---

## II. THINGS PLAYERS WILL FIND "NOT FUN"

**11. Being "snared" offers zero counterplay.**
When wizard strength falls below the casting threshold, the player can do nothing except wait. No ally can help. No item or emergency mechanic can accelerate recovery. The experience is: "You clicked something wrong and now you sit and watch the game for several ticks." This is pure passive punishment with no agency.

**12. Spell Reflect inflicts self-damage worse than doing nothing.**
Getting a hostile or war spell reflected back at "amplified damage" means the caster is worse off than if they had never attempted the spell. They spent mana, wizard strength, lost the spell entirely, and took amplified damage on top. Three bad outcomes from a single action. Players will find this deeply unfair, especially since the Spell Reflect buff on the target is invisible to attackers without Revelation.

**13. Chaos critical failure reflects your own spell at amplified damage.**
This is the same problem as Spell Reflect but worse: it can happen even without a defender's active buff. A Chaos League member casting aggressively can have their own Fireball hit themselves. In a game about managing a realm, having your own optimized strategy literally attack you feels absurd and punishing rather than fun risk-reward tension.

**14. Being under hostile spells with no dispel mechanic.**
If Plague, Insect Swarm, Earthquake, or Great Flood land on you, you wait 8 hours (or longer in wartime). There is no dispel, no counter-cast, no allied assistance. The only response is to ensure the attacker's spell fails in the first place. Once applied, all magical debuffs are completely non-interactive for the defender.

**15. Friendly spells are restricted to two specific realm roles.**
Most players can never cast beneficial spells on their own realmmates. Only the Grand Magister and Court Mage roles have this ability. A skilled, mana-rich wizard player with no role cannot contribute defensively to their realm through magic. This creates a large class of magical players who are permanently locked out of half the magical system.

**16. Arcane Ward and Illumination have a cooldown, creating unavoidable vulnerability windows.**
The primary magical defense (Arcane Ward) and spy defense (Illumination) for realmmates have a cooldown between recasts. This means there are periodic windows where a realmmate cannot be re-protected even if the defending team wants to. Attackers can time their strikes around these windows, while defenders feel helpless watching the gap.

**17. Rejuvenation being silently cancelled by a new war declaration is an invisible consequence.**
When Rejuvenation is active, a new war declaration cancels it immediately. Players who earned the recovery window through surviving a magical assault can have it wiped away by a diplomatic action — potentially one made by a realmmate without their knowledge. Losing a protective buff through an indirect political event is unintuitive and feels like a loss of control over one's own game.

**19. Mana cannot be shared or traded with allies.**
If your realm's primary wizard is mana-depleted, no ally can help. All resources in the game are individually held with no transfer mechanism for mana specifically. Team coordination is encouraged in every other system (military support, spell buffing via roles) but the fundamental fuel for magic is entirely siloed.

**20. The 3-day hostile spell restriction is a blunt, arbitrary delay.**
Players interested in offensive magic can't do anything for the first 3 days (72 ticks). This is a large chunk of time where magic-focused players have no meaningful action available in their chosen playstyle. The design intent is to let economies stabilize, but it creates a period of pure irrelevance for those players.

---

## III. POOR DESIGN CHOICES

**21. Same-type buff non-stacking with no in-UI warning.**
If a dominion has Ares' Call (+10% defense) active and casts a racial spell that gives +15% defense, only the +15% counts. The Ares' Call contributed nothing — it was a wasted cast. Without an explicit in-game notification that a spell is being overridden by a higher-value effect, players will regularly waste mana and wizard strength on redundant spells. The non-stacking rule penalizes spell experimentation.

**22. Amplify Magic is a button-before-button mechanic with shallow depth.**
The entire decision tree for Amplify Magic is: "Do I want +50% duration? If yes, spend 2× mana." There is no meaningful situational choice beyond "do I have extra mana right now?" It adds complexity to the spell list without adding strategic depth. The spell is essentially a mana-dump button for players with surplus mana, not a thoughtful tactical option.

**23. Burning and Lightning Storm create positive-feedback pile-on dynamics.**
Once Burning is applied, each subsequent Fireball deals more damage. This creates an optimal attack pattern of "spam Fireball immediately after landing the first one." The defender takes escalating damage with no ability to interrupt the chain. The attacker is rewarded purely for executing the same action repeatedly with increasing returns. This is snowballing mechanics at their most punishing.

**24. Status effect expiry triggers Rejuvenation, which is cancelled by a new war declaration.**
Three cascading effects from a single timer expiry is far too much interconnectedness. Rejuvenation's immunity to spell damage during recovery is reasonable. Giving it a population growth bonus is reasonable. But having it silently cancelled by a new war declaration is an extreme consequence that players cannot plan around or react to. A protective buff disappearing through a diplomatic event is mechanical happenstance that players have no direct control over.

**25. Disband Spies (a hostile spell) hard-counters an entire investment category.**
Disband Spies converts enemy spies into draftees, effectively destroying the espionage investment of a focused spy player. A single successful cast can undo hours of spy training decisions. One system (magic) should not have an instant-nuke for an entire parallel system (espionage). This is not meaningful counter-play; it's a cross-system hard counter with no equivalent.

**27. War spells require war or recent invasion, creating mechanical dependency chains.**
Fireball and Lightning Bolt — two of the three most impactful spells — are locked behind war state or recent military action. This forces magic-focused players to either coordinate realm-level war declarations (requiring political capital) or personally invade targets first (requiring military investment). Races designed around offensive magic are dependent on systems outside their core identity just to access their best spells.

**28. Wizard strength and wizard ratio are too easy to conflate.**
Two different, equally critical statistics both contain the word "wizard" and both affect spell success. New players will regularly confuse "I have low wizard *strength*" (can't cast because stamina depleted) with "I have low wizard *ratio*" (spells fail because of relative weakness). The naming creates persistent cognitive overhead.

**29. Mana cost scaling with total land means planning future cast costs is difficult.**
Since mana cost = multiplier × total land, a dominion growing rapidly will find that spells that were affordable last week are now 30% more expensive in absolute terms. While mana production scales too, the moving target makes it difficult to budget for major casting campaigns during periods of active land growth.

**30. The Chaos system is only available to Chaos League members, creating gameplay access inequality.**
The most interesting mechanical wrinkle in the magic system — risk/reward tension through chaos accumulation, critical successes, and reflected spells — is locked entirely behind a specific realm organization. The majority of players never interact with this system at all. A compelling mechanical idea is siloed to one org when it could enrich the experience for everyone.

**31. Cyclone double-damages unowned wonders, incentivizing destroying your own side's wonder.**
The "double damage on unowned wonders" rule creates a perverse incentive: sometimes it's better to destroy a wonder than to hold it, specifically so it becomes unowned and easier to attack with Cyclone. Players destroying their own side's wonder for tactical advantage is a mechanical exploit masquerading as strategy.

**32. Critical success (1.5× damage) and critical failure (self-reflection) exist simultaneously.**
War spells can crit for bonus damage or fail-crit for self-damage. This creates an extreme variance range on a single cast: normal outcome, 50% bonus damage, or full self-reflection with amplified damage. This is too wide a variance band for a persistent strategy game where players plan around consistent outcomes.

---

## IV. UNINTUITIVE MECHANICS

**33. Spell Reflect reflects at *amplified* damage, with no design rationale.**
Why does a reflected spell hit the caster harder than the original target would have received? The "amplified" component serves the punishment function but lacks clear in-world logic. Players discovering this for the first time will feel the game is punishing them arbitrarily. "Your spell bounced back and hit you harder than it would have hit them" reads as unfair rather than strategic.

**34. War declaration cancels Rejuvenation — three degrees of indirection.**
Even experienced players will forget this exists until the moment their recovery buff unexpectedly disappears. This is the kind of mechanic that requires wiki research to discover and never feels like a natural discovery within the game itself.

**35. Archmages count as 2× wizards in the ratio calculation.**
This is a hidden conversion factor inside a deceptively simple formula. A player who looks at "I have 500 wizards and 100 archmages" needs to know the effective count is actually 700. This math is invisible without reading documentation, making archmage investment value opaque to anyone not already studying the underlying formulas.

**36. Wizard mastery provides vague, uncommunicated bonuses.**
"Up to a bonus of 2 points per tick at maximum mastery" for wizard strength recovery. What is maximum mastery? How many successful offensive casts does it require? What is the intermediate bonus at 50% mastery? Players cannot evaluate whether their mastery level is meaningful without access to the underlying formulas, making mastery feel like a background number rather than a meaningful progression system.

**37. Resilience adds recovery when snared, creating a counter-intuitive recovery ramp.**
Being snared (bad) gives you Resilience (good), which accelerates recovery. While this is actually good design intent (preventing permanent lockout), it's counterintuitive: the penalty state secretly contains a recovery mechanism. Players who don't know about Resilience may panic-recast everything, waste strength further, and not understand why they recover faster once fully snared.

**38. Friendly spell cooldowns have no clear timer communication.**
Arcane Ward and Illumination have cooldowns between recasts. If a player attempts to recast before the cooldown expires, what happens? Is there a clear countdown? The documentation mentions cooldowns exist but doesn't specify durations, which suggests potential UX confusion in-game where players attempt to cast and receive a silent failure.

**39. Success formula is an opaque exponential curve.**
Players cannot estimate their casting success rate from looking at their stats. The "exponential function of relative wizard ratio" produces non-linear results that are impossible to intuit. A player with 1.1× the target's wizard ratio might think "I'm 10% stronger, I should have decent odds" but the actual success rate depends entirely on the curve shape. Without a visible success % calculator, decisions are flying blind.

**40. Energy Mirror reduces both "incoming damage" and "duration" — two different effects on two different spell types.**
The spell reduces damage (for war spells) and duration (for hostile/debuff spells), but these are meaningfully different effects on mechanically different spell types. Players must understand which type applies to understand what their protection actually does, and there's no obvious reason why both effects come from a single self-spell.

**41. Fool's Gold only protects platinum by default — ore/lumber/mana protection requires a tech unlock.**
The base version protects one resource. A tech upgrade extends it to four. This means the strength of this defensive spell is hidden behind a technology prerequisite players may not know to research. Players using Fool's Gold without the tech may believe they're protected when they are not — for ore, lumber, and mana theft.

---

## V. STIFLED PLAYER INTERACTION

**42. Non-role realm members cannot contribute magically to their team.**
Unless you are Grand Magister or Court Mage, your magic is purely self-serving. A realm with 12 members might have 10 players watching a realmmate get magically hammered with no ability to help through the magic system. Team cooperation through magic is extremely limited.

**43. No magical support for allies under military pressure.**
There are no support spells — no way to temporarily boost a realmmate's defensive power through magic. No equivalent to "I'll buffer your defense with an emergency spell before they invade." Magic's friendly spell toolkit is entirely limited to preventing magic and spy ops, not actual military support.

**44. War declaration is required for the most impactful spells, but war is a realm-level decision.**
Individual players cannot unilaterally gain access to war spells. War requires a realm declaration, which requires political agreement within the team. A player who wants to cast Fireball on an enemy must convince their entire realm to declare war first. This creates extreme dependence on social coordination for what should be an individual player's tactical option.

**46. Surreal Perception creates a one-sided information advantage with no counter.**
A player with Surreal Perception sees all successful operations against them, including source identity. There is no countermeasure — no cloaking spell, no way to obscure your magical identity. Casting against a Surreal Perception target is guaranteed to reveal you, which may have diplomatic consequences. Players may opt out of casting entirely against such targets, reducing interaction.

**47. The 3-day restriction creates a protected honeymoon period followed by a sudden opening.**
Day 3 hits and suddenly the game explodes with hostile casting activity from everyone simultaneously. This creates a chaotic rush rather than organic escalation. Players who weren't paying attention at the exact 3-day mark may get hit with a barrage before they've cast their first Fool's Gold or Energy Mirror, despite theoretically having 3 days to prepare.

**48. Spell Reflect's 3-hour duration is easily scouted and circumvented.**
An attacker with Revelation (which reveals active spells) can simply check if Spell Reflect is active and wait 3 hours. The defensive spell has a known, trackable expiry. It doesn't create genuine uncertainty — it just delays the attack slightly. This makes Spell Reflect feel performative rather than genuinely protective.

**49. Being "snared" removes a player from competitive interaction for multiple ticks.**
A player who gets Magic Snare'd or over-casts themselves cannot cast self-buffs before an invasion, cannot recast expiring buffs, and cannot contribute offensively. Multiple hours of complete magical irrelevance, with no ally mechanism to compensate. This is total removal from one entire game system.

**50. Hostile spells are too marginal to incentivize investment.**
8-hour debuffs to food production, gem production, or boat production create small inconveniences. They are not dramatic enough to feel worth the investment in wizard ratio, the mana cost, and the risk of failed casts and wizard losses. Players will correctly perceive that military pressure is more effective per resource spent, so hostile spells become a "nice to have" that rarely influences game outcomes. The spells don't create meaningful interaction because they don't hurt enough to matter.

---

## VI. RACIAL IMBALANCE IN THE MAGIC SYSTEM

**51. Unholy Ghost (Dark Elf/Spirit) is categorically more powerful than other racial spells.**
"Enemy draftees do not contribute DP in invasions against you" is an enormous mechanical effect — it essentially removes a significant portion of every defender's baseline DP at the cost of a single self-spell. Compared to "food production bonus" or "ore production bonus" racial equivalents, this is a wildly asymmetric power level.

**52. Erosion (Merfolk/Lizardfolk) may actively hurt non-water-optimized builds.**
If a Merfolk player captures Plains or Mountain land, Erosion converts it to water. But the player may have specifically wanted that Plains for Farms or that Mountain for Ore Mines. The racial spell can actively fight against the player's construction strategy by forcing a land type they didn't want. A race's defining spell can work against them.

**53. Immortal wizard perks (Dark Elf, Spirit, Vampire, Demon) create a fundamental asymmetry.**
These races can fail offensive spells without losing wizards. Every other race pays a casualty cost for failed casts. This is not a small perk — it fundamentally changes the risk calculus of offensive magic. Magic-focused races without immortal wizards are playing an entirely different (and more punishing) game.

**55. Racial spell mana costs punish players precisely when competitive pressure peaks.**
Since mana cost scales with total land, racial spells become most expensive in the late round when dominions are largest — exactly when competitive play is most intense and those spells matter most. Players are squeezed hardest by their own signature spells at the worst possible time.

**56. Death and Decay (Undead) has significant downsides that can trap new players.**
The spell accelerates food and lumber decay, which damages the caster's resource economy. New Undead players may cast this expecting the zombie conversion benefit and find their food supply collapsing instead. The cooldown prevents immediate course correction. A racial spell that can be a trap for its own race is poor design.

---

## VII. SYSTEMIC AND META CONCERNS

**57. Magic investment cannibalizes the population that could be military.**
Wizards and archmages are trained from draftees and occupy population. Every wizard is a military unit that wasn't trained. In a game where military size determines invasion viability, being a "wizard specialist" means being militarily weak and thus a target. The dominions most invested in magic are simultaneously the most vulnerable to military attack.

**58. Wizard mastery accumulates only through successful offensive casts, compounding the advantage of the already-strong.**
Mastery is earned by successfully casting against targets you can already beat. If you're behind on wizard ratio (failing most casts), you can't earn mastery to recover. The reward goes to those already winning, providing another compounding advantage in a system already prone to runaway leaders.

**59. There are no defensive war spells — the defender is always reactive.**
When under a war spell campaign, you can reduce damage through buildings and tech but cannot retaliate magically in kind without your own war access. There is no purely defensive war spell — no magical barrier, no magical counter. The defender is always passive.

**60. Mana decay removes all strategic stockpile value.**
In most strategy games, resources can be saved for decisive moments. In this system, hoarding mana for a big offensive is self-defeating — you lose 2% every tick. The optimal play is always to spend immediately, removing any "save for the right moment" strategic layer from magical resource management.

**61. Friendly spell role restriction creates single points of failure.**
If the realm's Grand Magister goes inactive or gets destroyed, the realm loses the ability to cast Arcane Ward and Spell Reflect on members in need. A single player's absence removes an entire defensive dimension from the realm. This creates extreme dependence on specific individuals in what is supposed to be a cooperative team system.

**62. The Chaos system's risk/reward being exclusive to Chaos League makes magic feel flat for everyone else.**
The Chaos system — with its critical successes, chaos accumulation, and risk of self-reflection — is a genuinely interesting mechanic with risk-reward tension. Making it exclusive to one organization means the majority of players experience a flat, non-interactive casting system with no equivalent dynamic.

**63. Spell Reflect's single-use-per-cast is exploitable via probing.**
An attacker can intentionally cast a cheap hostile spell to consume the Spell Reflect, then immediately follow with a powerful war spell before it can be recast. The one-use-per-cast design is exploitable in a way that makes the defense weaker than it looks.

**64. Wizard strength recovery is hard-capped even with maximum investment.**
The maximum bonus from wizard mastery is 2 points per tick. Recovery from a full snare takes a predictable and calculable number of ticks regardless of further investment. This creates a recovery ceiling that makes "wizard recovery" builds underpowered — there's no way to meaningfully specialize in resilience.

**65. Mana production tied to specific land-type buildings means magic builds must be committed to early.**
A player who wants to maximize mana must heavily invest Swamp land in Towers. This commitment is made early and is difficult to undo. Players who discover mid-round that they want to shift toward magic have no practical path to do so.

**66. The magic system has no equivalent to military morale for strategic depth.**
Military has morale as a meaningful resource that deteriorates with overuse and recovers over time, shaping campaign planning. Magic has wizard strength (similar concept) but it lacks the same strategic weight. Magic feels mechanically thinner than military as a result.

**67. Late-round magic power scaling falls behind military scaling.**
In the late round, military power compounds through trained elite units, tech bonuses, and land growth. Magic power grows only through tech and wonders — there is no equivalent to "more land = automatically more military options." Magic-focused builds fall off in relative power during the round's decisive phase.

---

## Priority Summary

| Priority | Issue |
|---|---|
| Critical | Ratio system is self-reinforcing — leaders compound advantages, losers get locked out (#1, #2) |
| Critical | No dispel mechanic — once debuffed, defenders are purely passive until expiry (#14) |
| Critical | Friendly spells restricted to 2 realm roles — most players cannot contribute to team defense (#15, #42) |
| Critical | War declaration cancelling Rejuvenation is unintuitive and removes player agency over recovery (#17, #24) |
| High | Hostile spells are too low-impact to justify the investment required to cast them reliably (#50) |
| High | Burning/Lightning Storm snowball dynamics make war spell defense feel hopeless (#23) |
| High | Immortal wizard perk creates a two-tiered magic experience between races (#53) |
| High | Mana decay removes all strategic stockpiling decisions (#60) |
| High | Wizard losses on failure punish weaker players for attempting to compete (#2) |
| Medium | Non-stacking spell buffs waste casts with no UI feedback (#21) |
| Medium | Spell Reflect is trivially circumvented with a Revelation + 3-hour wait (#48, #63) |
| Medium | Wizard strength vs. wizard ratio naming confusion (#28) |
