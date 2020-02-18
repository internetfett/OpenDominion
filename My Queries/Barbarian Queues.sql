SELECT
d.name,
r.name,
dq.*
FROM
  dominion_queue as dq
  JOIN dominions as d on dq.dominion_id = d.id
  JOIN races as r on d.race_id = r.id
WHERE
  d.round_id = 18
  and r.name = 'Barbarian'
