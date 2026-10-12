PREPARE group_prime_insert_010 (bigint, bigint) AS
    INSERT INTO groups_prime (group_id, user_id)
         VALUES              ($1, $2)
      RETURNING group_id;