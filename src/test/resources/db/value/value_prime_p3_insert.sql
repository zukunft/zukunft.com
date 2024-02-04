PREPARE value_prime_p3_insert (bigint, bigint, bigint, bigint, numeric) AS
    INSERT INTO values_prime
                (phrase_id_1, phrase_id_2, phrase_id_3, user_id, numeric_value, last_update)
         VALUES ($1, $2, $3, $4, $5, Now())
    RETURNING phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4;