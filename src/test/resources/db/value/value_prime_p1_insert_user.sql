PREPARE value_prime_p1_insert_user
    (bigint, bigint) AS
    INSERT INTO user_values_prime
                (phrase_id_1, user_id)
         VALUES ($1, $2)
    RETURNING phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4;