PREPARE value_text_prime_p4_insert_11000_user (smallint, smallint, smallint, smallint, bigint, bigint, text) AS
    INSERT INTO user_values_text_prime
                (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id, text_value, last_update)
         VALUES ($1, $2, $3, $4, $5, $6, $7, Now())
    RETURNING phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4;