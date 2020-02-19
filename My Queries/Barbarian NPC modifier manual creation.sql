UPDATE
  `dominions`
SET
  `npc_modifier` = `land_plain` + `land_mountain` + `land_swamp` + `land_cavern` + `land_forest` + `land_hill` + `land_water`
WHERE `dominions`.`race_id` = 46;
