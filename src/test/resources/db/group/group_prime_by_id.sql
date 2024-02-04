PREPARE group_prime_by_id (bigint) AS
    SELECT group_id,
           group_name,
           description
      FROM groups_prime
     WHERE group_id = $1;