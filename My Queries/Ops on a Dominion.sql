SELECT
  d.id,
  d.name,
  o.*
FROM
  info_ops as o
  JOIN dominions as d on o.target_dominion_id = d.id
WHERE
  d.name = ''
