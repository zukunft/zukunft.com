PREPARE group_by_name FROM
   'SELECT group_id,
           group_name,
           description
      FROM `groups`
     WHERE group_name = ?
UNION
    SELECT group_id,
           group_name,
           description
      FROM groups_prime
     WHERE group_name = ?
UNION
    SELECT group_id,
           group_name,
           description
      FROM groups_big
     WHERE group_name = ?';
