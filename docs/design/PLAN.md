# Game Design Documentation Plan

## Goal

Produce a thorough, design-level description of every game system in OpenDominion.
Documents describe *how systems work and why* — not just what values are configured.
Avoid hard-coding specific numeric values where possible; prefer ratios, relationships,
and design intent, since balance numbers change frequently.

---

## Source Material Per Topic

Each document should be built from the following primary sources:

| Topic | YAML / JSON | Helpers | Calculators | Action Services | Scribes Views |
|---|---|---|---|---|---|
| 01 Races & Units | `app/data/races/` | `UnitHelper`, `RaceHelper` | `MilitaryCalculator` (unit stats sections) | — | `scribes/units` |
| 02 Land & Construction | — | `BuildingHelper`, `LandHelper` | `LandCalculator`, `ConstructionCalculator` | `ConstructionActionService` | `scribes/construction`, `scribes/land` |
| 03 Population & Resources | — | `PopulationHelper` | `PopulationCalculator`, `ProductionCalculator` | — | `scribes/resources` |
| 04 Military | `app/data/races/` (units) | `UnitHelper` | `MilitaryCalculator` | `TrainActionService`, `ReleaseActionService`, `InvasionService` | `scribes/military` |
| 05 Magic | `app/data/spells.yml` | `SpellHelper` | `SpellCalculator` | `CastActionService` | `scribes/magic` |
| 06 Espionage | — | `EspionageHelper` | `EspionageCalculator` | `EspionageActionService` | `scribes/espionage` |
| 07 Heroes | `app/data/heroes.yml`, `app/data/heroes/` | `HeroHelper` | `HeroCalculator` | `HeroActionService` | `scribes/heroes` |
| 08 Technology | `app/data/techs/` | `TechHelper` | `TechCalculator` | `TechActionService` | `scribes/techs` |
| 09 Wonders | `app/data/wonders.yml` | `WonderHelper` | `WonderCalculator` | `WonderActionService` | `scribes/wonders` |
| 10 Realms & Diplomacy | — | `RealmHelper` | — | `RealmFinderService`, `RealmActionService` | — |
| 11 Round Structure | `app/data/round_leagues.json` | — | `RoundCalculator` | — | `scribes/round` |

---

## Document Structure Template

Each file should follow this structure:

```
# [System Name]

## Overview
One-paragraph design summary: what the system does and its role in the game.

## Core Concepts
Key terms and entities the system introduces.

## How It Works
Subsections per major mechanic. Describe the logic flow, inputs, outputs,
and how pieces interact. Reference formulas in prose (e.g. "output scales
with X and is reduced by Y") without embedding specific constants.

## Race / Unit Variations (where applicable)
How different races or configurations alter the system's behavior.

## Interactions With Other Systems
Cross-references: what this system feeds into or depends on.

## Player Decision Space
What choices does the player make within this system? What are the tradeoffs?
```

---

## Execution Order & Rationale

Systems are documented in dependency order — foundational systems first so
cross-references can point forward to later documents.

1. **Races & Units** — everything else references unit types and race perks
2. **Land & Construction** — buildings underpin population, production, and defense
3. **Population & Resources** — the economic engine all military/magic systems consume
4. **Military** — invasion mechanics build on units, land, and resources
5. **Magic** — overlaps with military offensively; depends on mana (resources)
6. **Espionage** — parallel to magic; spy-based offense/defense
7. **Heroes** — character layer on top of military/magic/espionage
8. **Technology** — late-game multipliers across all systems
9. **Wonders** — realm-level objectives tying all systems together
10. **Realms & Diplomacy** — the social/organizational layer
11. **Round Structure** — the temporal frame that governs everything

---

## Notes

- Prefer design intent language: "scales with", "is limited by", "penalizes"
- Callout boxes (`> **Note:**`) for important edge cases or gotchas
- Link between documents using relative paths (`[Military](04-military.md)`)
- Flag anything that appears inconsistent between YAML data and calculator logic
