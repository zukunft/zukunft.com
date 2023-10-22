PREPARE value_prime_user_insert (bigint, bigint) AS
    INSERT INTO user_values_prime
                (group_id, user_id)
         VALUES ($1, $2);