-- 1. Get all IPs associated with a dominion.
-- 2. Get all dominions with the same IPs in the current round

CREATE TEMPORARY TABLE ips
  SELECT
    ua.ip
  FROM
    user_activities as ua
    JOIN users as u on ua.user_id = u.id
    JOIN dominions as d on d.user_id = u.id
    JOIN rounds as r on d.round_id = r.id
  WHERE
    d.name = 'Misery'
    and r.number = 15;

SELECT
  d.name,
  u.display_name,
  u.email,
  ua.ip,
  count(distinct ua.id) as 'actions on ip'
FROM
  dominions as d
  JOIN users as u on d.user_id = u.id
  JOIN user_activities as ua on ua.user_id = u.id
  JOIN rounds as r on d.round_id = r.id
WHERE
  1=1
  and ua.ip in (SELECT * FROM ips)
  and r.number = 15
GROUP BY
  d.name,
  u.display_name,
  u.email,
  ua.ip
