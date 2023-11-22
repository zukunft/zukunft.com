PREPARE value_p1_user_insert (bigint, bigint) AS
    INSERT INTO user_values_prime
                (phrase_id_1, user_id)
         VALUES ($1, $2)
    RETURNING phrase_id_1;