UPDATE
  `dominions`
SET
  `peasants` = ((`land_plain`+`land_mountain`+`land_swamp`+`land_cavern`+`land_forest`+`land_hill`+`land_water`)*10) -- Set 10 peasants per acre
WHERE
  `round_id` = 16 -- Current round
  and `race_id` = 46; -- Barbarians
