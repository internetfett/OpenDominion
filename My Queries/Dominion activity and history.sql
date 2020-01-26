SELECT
  d.id,
  d.name,
  dh.event,
  dh.delta,
  ge.type
FROM
  dominions as d
  JOIN dominion_history as dh on d.id = dh.dominion_id
  JOIN game_events as ge on d.id = ge.target_id
WHERE
  1=1
  and dh.event != 'tick'
  and d.name = 'Misery'
