PREPARE group_prime_by_id FROM
'SELECT group_id,
           group_name,
           description
      FROM groups_prime
     WHERE group_id = ?';