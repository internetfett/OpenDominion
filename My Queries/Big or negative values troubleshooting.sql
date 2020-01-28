SELECT
  d.id,
  d.name,
  r.name,
  d.resource_food,
  d.stat_total_food_production,
  dt.resource_food,
  dt.resource_food_production
FROM
  dominions as d
  JOIN dominion_tick as dt on d.id = dt.dominion_id
  JOIN races as r on d.race_id = r.id
WHERE
  d.round_id = 16;

  SELECT
    d.id,
    d.name,
    r.name,
    d.resource_mana,
    d.stat_total_mana_production,
    dt.resource_mana,
    dt.resource_mana_production
  FROM
    dominions as d
    JOIN dominion_tick as dt on d.id = dt.dominion_id
    JOIN races as r on d.race_id = r.id
  WHERE
    d.round_id = 16
