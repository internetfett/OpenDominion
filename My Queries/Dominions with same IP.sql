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
  r.number = 15
