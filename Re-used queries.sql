UPDATE
  `dominions`
SET
  `peasants` = ((`land_plain`+`land_mountain`+`land_swamp`+`land_cavern`+`land_forest`+`land_hill`+`land_water`)*10) -- Set 10 peasants per acre
WHERE
  `round_id` = 16 -- Current round
  and `race_id` = 46; -- Barbarians

---

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
  d.round_id = 16

---

-- Look at ops taken on a dominion

SELECT
  d.id,
  d.name,
  o.*
FROM
  info_ops as o
  JOIN dominions as d on o.target_dominion_id = d.id
WHERE
  d.name = 'Scenic Route'

--

-- Look at users with same IP

SELECT
  u.id as user_id,
  d.id as dominion_id,
  d.name,
  u.display_name,
  u.email,
  ua.ip,
  r.number
FROM
  dominions as d
  JOIN users as u on d.user_id = u.id
  JOIN user_activities as ua on ua.user_id = u.id
  JOIN rounds as r on d.round_id = r.id
WHERE
  u.id in (115,116,117)
  and r.number = 15

--

SELECT
  u.id as user_id,
  d.id as dominion_id,
  d.name,
  u.display_name,
  u.email,
  ua.ip,
  r.number,
  count(distinct ua.id)
FROM
  dominions as d
  JOIN users as u on d.user_id = u.id
  JOIN user_activities as ua on ua.user_id = u.id
  JOIN rounds as r on d.round_id = r.id
WHERE
  u.id in (115,116,117)
GROUP BY
  u.id,
  d.id,
  d.name,
  u.display_name,
  u.email,
  ua.ip,
  r.number
