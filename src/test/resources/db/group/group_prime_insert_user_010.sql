PREPARE group_prime_insert_user_010 (bigint, bigint) AS
    INSERT INTO user_groups_prime (group_id, user_id)
         VALUES                   ($1, $2)
      RETURNING group_id;