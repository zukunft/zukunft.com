PREPARE value_p1_insert (bigint, bigint, numeric) AS
    INSERT INTO values_prime
                (phrase_id_1, user_id, numeric_value, last_update)
         VALUES ($1, $2, $3, Now())
    RETURNING phrase_id1;