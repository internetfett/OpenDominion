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
  u.id in (116,117)
GROUP BY
  u.id,
  d.id,
  d.name,
  u.display_name,
  u.email,
  ua.ip,
  r.number
