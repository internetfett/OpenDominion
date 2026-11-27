<?php

/*
 * Relics: items a dominion finds and owns right away, then keeps, sells, lists to a realmmate
 * or equips on its hero. The code always calls the system Relics; what players see comes from
 * the display names below, so a themed round can say "Plunder" without touching any logic.
 */
return [
    'name_singular' => 'Relic',
    'name_plural' => 'Relics',

    /*
     * Name-generator and equipment data file in app/data. Do not change it during a round:
     * equipped relics look up their stats by item key in this file.
     */
    'data_file' => 'relics.json',

    'sources' => [
        'info_ops' => true,
        'search' => true,
        'attacks' => false,
    ],

    /*
     * Market value climbs from min to max over appreciation_hours. Realm sales use the min.
     */
    'rarities' => [
        'common' => ['label' => 'Common', 'class' => 'text-muted', 'min' => 5000, 'max' => 10000],
        'uncommon' => ['label' => 'Uncommon', 'class' => 'text-body-emphasis', 'min' => 10000, 'max' => 20000],
        'rare' => ['label' => 'Rare', 'class' => 'text-light-blue', 'min' => 25000, 'max' => 50000],
        'epic' => ['label' => 'Epic', 'class' => 'text-purple', 'min' => 37500, 'max' => 75000],
        'legendary' => ['label' => 'Legendary', 'class' => 'text-orange', 'min' => 50000, 'max' => 100000],
    ],

    /* Relics a dominion can hold at once, equipped ones included. */
    'inventory_cap' => 8,

    /* Hours for a relic's market value to climb from its rarity's min to its max. */
    'appreciation_hours' => 72,

    /* Share of the rarity's min paid for salvaging a bound relic. */
    'salvage_ratio' => 0.25,

    /* No relic can be found, bought or stolen until this many hours into the round. */
    'round_lock_hours' => 72,

    /* Chance for any find to rise one rarity tier. */
    'rarity_upgrade_chance' => 0.10,

    'info_ops' => [
        'base_chance' => 0.01,
        'chance_per_miss' => 0.0002,
    ],

    'search' => [
        /* Spy strength drained per hour at each intensity. */
        'intensities' => [1, 2],
        /* Offensive spy ratio below which Search makes no progress. */
        'threshold' => 0.25,
        /* Meter progress per hour = intensity * rate * (spy ratio - threshold). */
        'rate' => 2.8,
        'meter_max' => 100,
    ],

    'plunder' => [
        'chance' => 0.10,
        'min_range' => 75,
    ],

    'steal' => [
        'spy_strength_cost' => 10,
        /* Hours a dominion is on high alert, immune to Steal Relic, after a successful theft. */
        'high_alert_hours' => 12,
    ],
];
