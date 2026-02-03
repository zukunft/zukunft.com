PREPARE group_prime_insert_user_110 (bigint, text, bigint) AS
    INSERT INTO user_groups_prime
                (group_id, group_name, user_id)
         VALUES ($1, $2, $3)
    RETURNING group_id;