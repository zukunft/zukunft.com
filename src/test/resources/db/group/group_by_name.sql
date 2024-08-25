PREPARE group_by_name (text) AS
    SELECT group_id,
           group_name,
           description
      FROM groups
     WHERE group_name = $1
UNION
    SELECT group_id::text,
           group_name,
           description
      FROM groups_prime
     WHERE group_name = $1
UNION
    SELECT group_id,
           group_name,
           description
      FROM groups_big
     WHERE group_name = $1;