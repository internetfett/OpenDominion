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
| Disciplines | One rule-bending pick at level 3 | `feature/hero-progression` |
| Forms | A one-turn status named after the Form, granted after its opener action (level 7) | `feature/hero-progression` |
| Gear | The only source of combat stats (levels 5 and 9) | `feature/hero-progression` |
| Upgrade schedule | Even levels: dominion upgrades with combat stats removed. Odd levels: 3 Discipline / 5 Gear / 7 Form / 9 Gear. Data-driven slots. | `feature/hero-progression` |
| Ally actions | Defend on an ally is a guard (hits aimed at them strike you); Recover can heal an ally | `feature/hero-progression` |
| Borrowed heroes | Pilot a realmmate's hero in party-enabled encounters, live or practice; no owner reward | `feature/hero-progression` |
| Tactician + simulator | Game-theory AI and `hero-combat:simulate` for PvP balance. Analysis only, not in the game. | `feature/hero-progression` |

## Disciplines (level 3)

| Discipline | Rule | vs basic (seeds 101 / 5001) |
|---|---|---|
| Overpower | Attacks against a Defending target ignore 5 defense | 52.6 / 52.1% |
| Battle Fury | +4 attack / −2 defense per consecutive Attack, up to 3 stacks; any other action resets it | 58.4 / 53.8% |
| Attunement | Focus has no cooldown and stacks to 2 | 52.8 / 51.9% |
| Rejuvenation | Recover heals 80% now + 10% at the start of each of the next 3 turns; keeps the −5 defense penalty; recasting forfeits the remaining ticks | 60.8 / 60.7% |
| Riposte | Counter has no cooldown | 60.4 / 62.3% |
| Shadow Dance | Defend also grants +30 evasion; evaded unfocused hits deal 0 and grant Focus | 52.4 / 53.2% |

How they got here:

| Discipline | First version | Problem | Change |
|---|---|---|---|
| Iron Guard (replaced by Overpower) | Defending reflected damage | Defend already nullifies most hits, so reflecting is free damage | Replaced with Overpower |
| Battle Fury | +5 / −5 per stack | Volatile (44–53% across seeds) | 2:1 ratio, +4 / −2 |
| Attunement | Focus stacks to 2 | No gain (50%): Focus's cooldown made a second stack take 3 turns | Focus also loses its cooldown |
| Rejuvenation | 60% + 60%, no defense penalty | 72%: skipping the −5 penalty is worth ~15 HP per battle | Penalty restored; 80% + 3×10% |
| Riposte | No Counter cooldown, counters at 70% | 28%, worse than nothing | Counters at full strength |
| Shadow Dance | Dodge (+40 evasion) instead of doubled defense | 47%: a coin-flip dodge loses to a reliable block | Keeps the doubling, adds +30 evasion and Focus on a dodge |

## Forms (level 7)

| Form | After | Next turn | vs basic |
|---|---|---|---|
| Ambush | Counter | +5 attack | 57.5% |
| Feint | Attack | +10 evasion | 55.8% |
| Second Wind | Recover | +5 defense | 55.0% |
| Shield Bash | Defend | +5 attack | 54.6% |
| Arcane Ward | Focus | +5 defense | 51.7% |
| Spellweave | Focus | +5 counter | 49.6% |

Forms were measured once (120 battles) and not changed. The trigger is hidden; opponents see the status, which makes it a readable tell.

## Gear (levels 5 and 9, after tuning)

| Level 5 | Stats | | Level 9 | Stats |
|---|---|---|---|---|
| Greatsword | +2 atk, −5 eva | | Executioner's Axe | +3 atk, −5 eva |
| Dueling Blade | +3 counter, +5 eva | | Shadowfang Daggers | +4 counter, +8 eva |
| Spellfocus Staff | +4 focus | | Archmage's Wand | +6 focus |
| Plate Armor | +1 def, −5 eva | | Tower Shield | +2 def, −5 eva |
| Leathers | +8 eva | | Shadowweave Cloak | +12 eva |
| Warded Robes | +5 recover, +2 focus | | Vestments of Renewal | +4 recover, +5 hp |

The tuned gear scores 52–60% at level 5 and 55–69% at level 9. Attack and defense are worth about 5% per point, because the base damage margin is only 40 attack vs 20 defense. The first values (+4–6 attack, +3–5 defense) scored 74–99%.

---

## Discussed, not built

| Idea | Summary | Status |
|---|---|---|
| Tactician as the in-game player AI | One strategy with balanced/aggressive/defensive as weight profiles, plus a temperature setting to make it beatable | **Parked.** Concern: it could play better than real players, making manual play pointless. Fix the information leak first (strategies can see the full `Battle`). |
| Team (2v2) PvP | Needs ally Defend/Recover; raises focus fire and snowballing, Counter's changed role, heal stalling, partner visibility, pace with four players | Discussion only |
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
| Upgrade names | Warrior/mage/rogue flavor, no stored or shown "path" |
| Upgrade kinds in UI | Tooltips only |
| Forms | A status named after the Form, mostly simple stat boosts |
| Party encounters | Only new encounters designed for two; no automatic scaling |
| Borrowing | Any realmmate, any number at once; pilot keeps full credit |
| Daily XP timing | Round day (`daysInRound`), not calendar day or rolling 24h |
| Migrations | Kept to one combat-engine migration |
| Branching | Build here, overwrite magic-system's unlock UI at merge time |

## Open follow-ups

| Item | Notes |
|---|---|
| Rejuvenation still about 61% | Try 70/10/10/10 (100% total) |
| Spellweave and Arcane Ward weak | The two Focus Forms clash with the natural follow-up to Focus |
| Overpower, Attunement, Shadow Dance about 52% | Slightly below the 55–60% target |
| Tower Shield 69% | Strongest gear |
| No combination testing | Discipline + Form + Gear builds, and a fresh `--grid` run |
| First co-op encounter | Needed before borrowing is usable in game |
| magic-system merge | Take this branch's unlock logic and slot UI; update their level-8 tests |
