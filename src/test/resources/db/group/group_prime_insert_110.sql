PREPARE group_prime_insert_110 (bigint, text, bigint) AS
    INSERT INTO groups_prime
                (group_id, group_name, user_id)
         VALUES ($1, $2, $3)
    RETURNING group_id;