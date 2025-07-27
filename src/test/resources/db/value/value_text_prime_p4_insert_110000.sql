PREPARE value_text_prime_p4_insert_110000 (smallint, smallint, smallint, smallint, bigint, text) AS
    INSERT INTO values_text_prime
                (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, text_value, last_update)
         VALUES ($1, $2, $3, $4, $5, $6, Now())
    RETURNING phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4;