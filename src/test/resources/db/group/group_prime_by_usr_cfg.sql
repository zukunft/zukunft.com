PREPARE group_prime_by_usr_cfg (bigint, bigint) AS
    SELECT group_id,
           group_name,
           description
      FROM user_groups_prime
     WHERE group_id = $1
       AND user_id = $2;