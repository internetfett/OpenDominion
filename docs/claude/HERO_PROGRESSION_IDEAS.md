# Hero Progression: Ideas, Decisions and Balance Notes

A record of the hero combat and progression design work: what was built, what was discussed and set aside, and what's still open. For how the engine works, see [HERO_COMBAT.md](HERO_COMBAT.md).

Balance numbers come from `php artisan hero-combat:simulate`: Tactician vs Tactician, a level-9 hero with only the pick against a basic level-9 hero, with sides swapped halfway over the same seeds. Scores count wins plus half of draws; 50% means no edge. With 400 battles the margin of error is about ±2.5%.

---

## Built

| Area | What it is | Branch |
|---|---|---|
| Combat engine | Modular `src/HeroCombat`: simultaneous turns, N-vs-N teams, effects with duration/stacks/tags, ordered event hooks, team and field effects, a single damage pipeline, cooldowns and charges, seeded replays (`hero-combat:replay`) | `feature/hero-combat-v2` |
| Content port | 5 basic actions, class abilities and passives, all 18 original encounters (the unused wolf/bandit/imp/cultist "story" encounters were skipped) | `feature/hero-combat-v2` |
| Daily PvP XP | Flat 25 XP once per round day, win or lose, if your time bank never ran out | `feature/hero-combat-v2` |
| Techniques | Passive rules: Technique I (level 3) and Technique II (level 5), replacing Disciplines and Combos | `feature/hero-progression` |
| Shared statuses | Exposed, Marked, Staggered (plus Invigorated from Second Wind), cashed in by any attacker | `feature/hero-progression` |
| Class Abilities | Level 7: any class signature unlocked at class level 5 (active or not); uses the class icon | `feature/hero-progression` |
| Gear | The only source of combat stats (one pick at level 9) | `feature/hero-progression` |
| Upgrade schedule | Even levels: dominion upgrades with combat stats removed. Odd levels: 3 Technique I / 5 Technique II / 7 Class Ability / 9 Gear. Data-driven slots. | `feature/hero-progression` |
| Ally actions | Defend on an ally is a guard (hits aimed at them strike you); Recover can heal an ally | `feature/hero-progression` |
| Borrowed heroes | Pilot a realmmate's hero in party-enabled encounters, live or practice; no owner reward | `feature/hero-progression` |
| Tactician + simulator | Game-theory AI and `hero-combat:simulate` for PvP balance. Analysis only, not in the game. | `feature/hero-progression` |

Class abilities and passives reach heroes only as level-7 upgrades; `HeroClassLoadouts` no longer grants them by active class. Some are also used by PvE enemies (Hardiness, Shadow Strike).

---

## Redesign: Techniques and Class Abilities (built)

Disciplines and Combos merge into **Techniques** (passive rules). Class abilities and passives become **Class Abilities**, unlocked by class level. The Combo type and its machinery (`AbstractCombo`, `ComboStatus`, the Combo badge in `BattleView`, `combo=` in the simulator) go away.

Why: every Combo was "after action X, +N to one stat next turn", which is gear with a condition, not a rule. Spellweave and Wellspring never found a payoff worth taking.

### Design rules

| Rule | Detail |
|---|---|
| Useful in 1v1, better in 2v2 | No pick may need a partner to be worth taking. Partner-only ideas are parked until 2v2 exists |
| Reward the state, not your own action | Setups apply a shared status to the target; payoffs check the status, whoever applied it. In 1v1 you set up and cash in yourself; in 2v2 your ally can too |
| Same tier = exclusive | One pick per tier, so the strongest pairs must share a tier. Pairs across tiers can be combined and need simulator checks |
| Short buffs and debuffs are fine | One-turn statuses are allowed, as long as they're rules rather than plain stat sticks |
| Gear last | Gear moves to level 9 so it can later become a separate mechanic without reshuffling the other slots |
| No stances | Considered (e.g. a Bodyguard stance that guards an ally every turn); rejected |

### Pick schedule

| Level | Slot | Pool |
|---|---|---|
| 3 | Technique I | Self rules (6) |
| 5 | Technique II | Setup/payoff rules (5) |
| 7 | Class Ability | The signature of any class unlocked this round |
| 9 | Gear | Unchanged (6) |

Even levels keep the dominion upgrades.

### Shared statuses

Each lasts one turn and applies to attacks from anyone.

| Status | Effect | Notes |
|---|---|---|
| Exposed | −5 defense | Also used for one turn while channeling (Hungering Moon) |
| Marked | Takes +5 damage from attacks | New |
| Staggered | Can't Counter | New |

### Technique I (level 3)

| Technique | Rule | Source |
|---|---|---|
| Battle Fury | +4 attack / −2 defense per consecutive Attack, up to 3 stacks; any other action resets it | Discipline, kept |
| Mana Shield | +3 defense while Focused | Discipline, kept |
| Restoration | Recover heals 80% now + 10% at the start of each of the next 3 turns; works on an ally | Discipline, kept |
| Shadow Dance | Evading an attack grants Focus (only if not already Focused) | Discipline, kept |
| Backstab | +5 damage against a target that was Focused at the start of the turn | Discipline, kept |
| Feint | +10 evasion on a turn you Attack. No status; it applies to attacks resolving that same turn | Was a Combo (+10 evasion the turn after) |

### Technique II (level 5)

| Technique | Setup → status | 1v1 line | 2v2 bonus |
|---|---|---|---|
| Sunder | Hitting a Defending target → **Exposed** | Hit through their Defend, then hit harder | Ally hits the Exposed target |
| Shield Bash | Being hit while Defending → attacker **Staggered** | Defend → Attack with no Counter risk | Ally attacks safely too |
| Riposte | Landing a Counter → attacker **Marked** | Counter → Attack | Both hit the Marked target |
| Spellbrand | Landing a Focused attack → target **Marked** | Focus → Attack → Attack | Ally follows up on the big hit |
| Second Wind | After Recover, +5 attack next turn; an ally you healed gets it too | Recover → Attack | Healing your ally also buffs them |

Spellbrand is new. Cut: Overpower (Sunder replaces it), Spellweave, Wellspring.

### Class Abilities (level 7)

Unlocked by reaching **class level 5** in that class this round. The class doesn't need to be active: `heroes.class_data` already keeps experience for every class played. Some signatures are passives; they still take this slot.

| Class | Signature | Kind |
|---|---|---|
| Alchemist | Volatile Mixture: 150% attack, 20% chance to hit yourself | Action |
| Architect | Fortify: shield of 20, replaces Defend | Action |
| Blacksmith | Forge: +1 attack for the rest of the battle | Action |
| Engineer | Tactical Awareness: target −2 counter for the rest of the battle | Action |
| Scholar | Combat Analysis: target −1 defense for the rest of the battle | Action |
| Infiltrator | Shadow Strike: can't be evaded, +2 damage against a Defending target | Action |
| Farmer | Hardiness: survive the first lethal hit on 1 health | Passive |
| Healer | Mending: Focus increases Recover's healing | Passive |
| Sorcerer | Channeling: Focus stacks | Passive |
| Scion | Last Stand: +10% all combat stats at 40 health or less | Passive |

Forge, Tactical Awareness and Combat Analysis last the whole battle and stack. Two heroes debuffing one boss in 2v1 PvE is the intended synergy, but they likely need a stack cap to avoid stalls.

### Parked for 2v2

| Idea | Summary |
|---|---|
| Attunement | Focus can target an ally |
| Taunt | Enemies must attack you (the Provoked effect exists) |
| Fortify an ally | Shield an ally instead of yourself |

### First measurements (vs basic, 400 battles, seeds 101 / 5001)

| Pick | Score | Pick | Score |
|---|---|---|---|
| Battle Fury | 58.4 / 53.8% | Hardiness | 82.6 / 82.2% |
| Mana Shield | 55.1 / 55.2% | Last Stand | 72.2 / 74.8% |
| Restoration | 60.8 / 60.7% | Fortify | 71.2 / 72.0% |
| Shadow Dance | 55.1 / 55.9% | Shadow Strike | 55.2 / 58.1% |
| Backstab | 55.6 / 57.2% | Volatile Mixture | 53.0 / 54.6% |
| Feint | 51.7 / 56.6% | Forge | 52.0 / 51.6% |
| Sunder | 51.3 / 53.0% | Mending | 50.1 / 51.0% |
| Shield Bash | 50.8 / 48.6% | Channeling | 50.1 / 50.9% |
| Riposte | 54.0 / 52.2% | Tactical Awareness | 50.0 / 50.0% (never used) |
| Spellbrand | 52.9 / 51.9% | Combat Analysis | 50.0 / 50.0% (never used) |
| Second Wind | 58.9 / 58.1% | | |

The Tactician looks one turn ahead, and at first its setup value only counted its own attack and Focus, so it never picked Tactical Awareness or Combat Analysis and played blind to Exposed, Marked and Staggered. The setup value is now a damage edge against the opponent (see HERO_COMBAT.md); the affected picks were re-measured:

| Pick | Before | After damage-edge scoring |
|---|---|---|
| Battle Fury | 58.4 / 53.8% | 58.2 / 55.2% |
| Mana Shield | 55.1 / 55.2% | 53.5 / 55.2% |
| Sunder | 51.3 / 53.0% | 51.6 / 50.1% |
| Shield Bash | 50.8 / 48.6% | 50.7 / 52.5% |
| Riposte | 54.0 / 52.2% | 55.2 / 52.5% |
| Spellbrand | 52.9 / 51.9% | 53.0 / 51.4% |
| Forge | 52.0 / 51.6% | 51.6 / 51.4% |
| Tactical Awareness | 50.0 / 50.0% | 52.1 / 50.4% |
| Combat Analysis | 50.0 / 50.0% | 52.4 / 51.1% |
| Last Stand | 72.2 / 74.8% | 69.2 / 74.2% |

Seeing the statuses didn't lift the Technique II picks: they are weak in 1v1 on their values, not because the AI ignored them. The battle-long debuffs are now used but are worth little (−1 defense ≈ 5 points of edge against Focus's 10). Technique II setups are weak in 1v1 as expected for partner-oriented picks, but below the "useful in 1v1" rule. Hardiness, Last Stand and Fortify are far too strong.

### Simulator checks

| Check | Why |
|---|---|
| Every pick vs basic | Target 55–60% |
| Battle Fury + Riposte / Second Wind | Attack stacking across tiers |
| Backstab + Spellbrand | Both build on Focus |
| Shadow Dance + Spellbrand | Evasion grants free Focus, which feeds the Mark |
| Feint + Battle Fury | Both reward attacking every turn |
| 2v2 / 2v1 support | The simulator only runs 1v1; team value doesn't show up yet |

---

## History: Disciplines (level 3)

Two warrior, two mage and two rogue picks. Measured after the redesign (400 battles per seed):

| Discipline | Flavor | Rule | vs basic (seeds 101 / 5001) |
|---|---|---|---|
| Overpower | warrior | Attacks against a Defending target ignore 5 defense | 51.0 / 54.5% |
| Battle Fury | warrior | +4 attack / −2 defense per consecutive Attack, up to 3 stacks; any other action resets it | 58.2 / 57.5% |
| Restoration | mage | Recover heals 80% now + 10% at the start of each of the next 3 turns; keeps the −5 defense penalty; recasting forfeits the remaining ticks | 59.6 / 58.0% |
| Mana Shield | mage | While Focused, +3 defense | 55.5 / 53.1% |
| Shadow Dance | rogue | Evading an attack grants Focus (only if not already Focused) | 54.9 / 58.1% |
| Backstab | rogue | +5 damage against a target that was Focused at the start of the turn | 57.8 / 57.4% |

How they got here:

| Discipline | First version | Problem | Change |
|---|---|---|---|
| Iron Guard (replaced by Overpower) | Defending reflected damage | Defend already nullifies most hits, so reflecting is free damage | Replaced with Overpower |
| Battle Fury | +5 / −5 per stack | Volatile (44–53% across seeds) | 2:1 ratio, +4 / −2 |
| Attunement | Focus stacks to 2 | No gain (50%): Focus's cooldown made a second stack take 3 turns | Focus also loses its cooldown |
| Rejuvenation | 60% + 60%, no defense penalty | 72%: skipping the −5 penalty is worth ~15 HP per battle | Penalty restored; 80% + 3×10% |
| Riposte | No Counter cooldown, counters at 70% | 28%, worse than nothing | Counters at full strength |
| Shadow Dance | Dodge (+40 evasion) instead of doubled defense | 47%: a coin-flip dodge loses to a reliable block | Keeps the doubling, adds +30 evasion and Focus on a dodge |
| Riposte | Counter has no cooldown | Removed the attacker's safe window after a Counter, so attacking was never safe (60–62%) | Cut. The name moved to the Counter Combo; Mana Shield took the slot |
| Attunement | Focus has no cooldown and stacks to 2 | Too fundamental a change to a basic action | Cut; Backstab took the slot. Focus no longer stacks for heroes |
| Shadow Dance | +30 evasion on Defend, zero-damage dodges, Focus on a dodge | Four effects in one pick; Defend already blocks unfocused hits, so the evasion mostly didn't matter | One rule: evading grants Focus. Rewards evasion builds, not Defend |
| Rejuvenation | — | Shares its name with a spell | Renamed Restoration (and Arcane Ward's idea became Mana Shield for the same reason) |
| Mana Shield | +4 defense while Focused | 56.1 / 55.0%, but longer battles (up to 21 turns): Focus is held until an attack spends it | Kept at +3 so holding Focus doesn't become a stall |

Cut ideas for this slot: Focus-breaking Counter ("Spoiling Strike": opponent's Focus is cancelled if you Counter it), a landed Counter refunding its cooldown (still removes the window), Counter striking setup turns (Counter would beat everything but Defend), reduced damage from focused attacks (weakens Focus further and makes both rogue picks defensive).

## History: Combos (level 7)

Being replaced by Technique II (see the redesign above). Every action opened at least one Combo (Defend opened two). Measured (400 battles per seed):

| Combo | After | Next turn | Sequence | vs basic (seeds 101 / 5001) |
|---|---|---|---|---|
| Feint | Attack | +10 evasion | Mostly passive; evasion is wasted if you Defend next | 57.1 / 57.0% |
| Shield Bash | Defend | +5 attack | Defend → Attack | 56.9 / 56.9% |
| Spellweave | Defend | +10 focus | Focus → Defend → Attack | 56.4 / 54.8% |
| Riposte | Counter | +5 attack | Counter → Attack | 62.1 / 58.2% |
| Second Wind | Recover | +5 attack | Recover → Attack | 58.1 / 59.4% |
| Wellspring | Focus | +5 recover | Focus → Recover (hold Focus while they Defend) | 50.4 / 51.4% |

Candidate values tried: Spellweave +5 focus 51.6 / 52.8%; Wellspring +10 recover 52.2 / 51.0%; Riposte +4 attack 54.6 / 54.5%. Spellweave was raised to +10 because it shared Defend with Shield Bash; head to head it scored 49.8 / 49.8% at +10 (48.1% at +5, seed 101).

Pairing rules used to choose the table, still useful for Technique II:

| Rule | Cuts |
|---|---|
| An action can't pay its own stat (Counter, Focus, Recover can't repeat; Attack→attack is Battle Fury's job) | all same-stat pairs |
| The stat must matter next turn | Attack → focus (attacking spends Focus) |
| No automatic bonuses | Focus → attack (you attack after Focus anyway) |
| No turtling | Counter → defense, Defend → counter/recover, Counter → recover, Recover → counter |
| No duplicate jobs | Attack → defense (Feint's job), Counter → focus (Ambush's job), Recover → focus (same line as Wellspring) |
| No defense payoffs: too easily telegraphed | Arcane Ward, old Second Wind (+5 defense) |
| Too easily avoided | Attack → recover |
| Counter payoffs | Spellweave's old +5 counter clashed with attacking after Focus; Attack → counter duplicated the Attack/Counter cycle Ambush (now Riposte) already rewards |

## Gear (moving from level 5 to level 9)

Two picks per archetype, with generic names; unique items are planned elsewhere. Gear may later become a separate mechanic from level upgrades, which is why it moves to the last slot. Scores are a level-9 hero against a basic level-9 hero (400 battles, seeds 101 and 5001).

| Gear | Archetype | Stats | Score |
|---|---|---|---|
| Greatsword | Warrior | +2 atk, −10 eva | 59.4% |
| Plate Armor | Warrior | +1 def, −5 eva | 60.6% |
| Swordbreaker Dagger | Rogue | +6 counter | 59.2% |
| Hooded Cloak | Rogue | +8 eva | 57.5% |
| Crystal Wand | Mage | +6 focus | 58.7% |
| Vestments of Renewal | Mage | +4 recover, +5 hp | 61.3% |

Attack and defense are worth about 5% per point, because the base damage margin is only 40 attack vs 20 defense, so Greatsword pays for its attack with −10 evasion. Health is also strong: +5 hp alone moves Vestments about 6%, and +10 hp alone scored 68%.

| Tested and rejected | Score |
|---|---|
| Greatsword +3 atk, −5 eva | 69% |
| Greatsword +2 atk, −5 eva | 64% |
| Plate Armor +2 def, −5 eva | 71% |
| Swordbreaker Dagger +4 / +8 counter | 55% / 61% |
| Hooded Cloak +10 / +12 eva | 61% / 62% (one seed 65%) |
| Crystal Wand +4 / +8 focus | 52% / 61% |
| Vestments +4 recover only | 55% |
| Vestments +8 recover / +4 recover, +10 hp | 78% / 68% |

Cut from the old two-tier list: Executioner's Axe, Tower Shield, Dueling Blade, Leathers, Spellfocus Staff, Warded Robes. Shadowfang Daggers, Shadowweave Cloak and Archmage's Wand were renamed to the generic Swordbreaker Dagger, Hooded Cloak and Crystal Wand (Swordbreaker became counter only).

---

## Discussed, not built

| Idea | Summary | Status |
|---|---|---|
| Tactician as the in-game player AI | One strategy with balanced/aggressive/defensive as weight profiles, plus a temperature setting to make it beatable | **Parked.** Concern: it could play better than real players, making manual play pointless. Fix the information leak first (strategies can see the full `Battle`). |
| Team (2v2) PvP | Needs ally Defend/Recover; raises focus fire and snowballing, Counter's changed role, heal stalling, partner visibility, pace with four players | Discussion only |
| Stances | Switchable modes, e.g. Bodyguard (always guard an ally, −attack, a turn to switch) | Rejected; only worth it if mechanically interesting |
| Insight meter | Fills on correct reads (Counter landing, Defend absorbing) and unlocks a signature move | Not chosen |
| Earned traits | The hero develops traits from how it fights over the round | Not chosen |
| Dominion-based bonuses | Small tilts from land, buildings or race | Not chosen (PvP fairness risk) |
| Damage behaviors | Crushing / Piercing / Lingering instead of magic vs physical | Not chosen |
| Owner reward for borrowed heroes | e.g. a raid score bonus | Dropped (side effects on tactic limits, realm wounds and rewards); nothing for now |

### 2v2 PvP issues identified

| Issue | Notes / options |
|---|---|
| Focus fire and snowballing | Both enemies hit one target; then it's 2v1. Counterweights: Guard, Riposte for allies, a downed state with revive |
| Counter's role | Wasted if nobody targets you, doubled under focus fire; option to let Counter trigger when your ally is hit |
| Heal stalling | Alternating heals on one target; options: weaker ally heals, a "recently healed" debuff, a turn cap with sudden death |
| Coordination | Teammates must see each other's queues; opponents must not |
| Pace | Four players' time banks; maybe a per-turn deadline |
| Automated partners | Need basic ally logic (heal the wounded ally, guard when hurt) |
| Matchmaking and ratings | Team formation, leaving, rating spread within a team |

### Tactician design (for reference)

| Part | Approach |
|---|---|
| Information | Public state only: health, stats, visible effects, cooldowns |
| Payoffs | Preview every pairing of both sides' legal actions on a copied state; evasion enumerated exactly |
| Solving | Exact zero-sum matrix solve (simplex). Regret matching was tried first but converged too slowly to be accurate. |
| Scoring | Damage dealt/taken, healing, change in attack potential, win/loss chance |
| Personality | Weight profiles plus a temperature setting toward uniform play |
| Strength | Beats today's `balanced` preset about 60–40 |

---

## Key decisions

| Topic | Decision |
|---|---|
| Content definitions | PHP classes, registered explicitly in `HeroCombatServiceProvider` |
| Turn model | Simultaneous turns with per-player time banks |
| Upgrade names | Warrior/mage/rogue flavor, no stored or shown "path". Names must not reuse spell names (`app/data/spells.yml`) |
| Upgrade categories | Techniques (passive rules, two tiers) and Class Abilities; Disciplines and Combos are retired |
| Techniques | Bend a rule without removing a basic action's cooldown; useful in 1v1, better in 2v2 |
| Synergy | Shared one-turn statuses (Exposed, Marked, Staggered) that any attacker can cash in |
| Class Abilities | Unlocked at class level 5 in any class played this round, not only the active one |
| Gear | Last slot (level 9), so it can become a separate mechanic later |
| Upgrade kinds in UI | Tooltips only |
| Party encounters | Only new encounters designed for two; no automatic scaling |
| Borrowing | Any realmmate, any number at once; pilot keeps full credit |
| Daily XP timing | Round day (`daysInRound`), not calendar day or rolling 24h |
| Migrations | The combat-engine migration is already applied and must not be edited; later columns (controllers, daily PvP reward) live in `2026_10_29_150132_add_controllers_and_pvp_rewards_to_heroes` |
| Branching | Build here, overwrite magic-system's unlock UI at merge time |

## Open follow-ups

| Item | Notes |
|---|---|
| Existing heroes' picks | Heroes holding Overpower, Spellweave or Wellspring (now inactive) keep a dead pick; a hero that took gear at level 5 now has it as a level-9 pick, leaving level 5 open. Needs a migration or reset |
| Stale upgrade rows | Data sync never deactivates keys removed from `heroes.yml`; keep retired upgrades in the file with `active: false` |
| Stack caps | Forge, Tactical Awareness, Combat Analysis |
| Riposte, Second Wind values | As Combos they ran 58–62% at +5 attack; the Marked/ally versions need fresh numbers |
| Vestments of Renewal about 61% | Slightly above target; battles run longest (about 22 turns) |
| Combination testing | See the simulator checks above, plus a fresh `--grid` run |
| 2v2 in the simulator | Needed to measure team value |
| First co-op encounter | Needed before borrowing is usable in game |
| magic-system merge | Take this branch's unlock logic and slot UI; update their level-8 tests |
