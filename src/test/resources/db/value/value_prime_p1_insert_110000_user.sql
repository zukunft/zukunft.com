PREPARE value_prime_p1_insert_110000_user
    (smallint, bigint, numeric) AS
    INSERT INTO user_values_prime
                (phrase_id_1, user_id, numeric_value, last_update)
         VALUES ($1, $2, $3, Now())
    RETURNING phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4;