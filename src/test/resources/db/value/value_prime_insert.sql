PREPARE value_prime_insert (bigint, bigint, text, text) AS
    INSERT INTO values_prime
                (group_id, user_id, numeric_value, last_update)
         VALUES ($1, $2, $3, $4);