PREPARE result_prime_p3_insert_111000 (smallint, smallint, smallint, smallint, bigint, numeric, bigint) AS
    INSERT INTO results_prime (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, user_id, numeric_value, last_update, source_group_id)
         VALUES ($1, $2, $3, $4, $5, $6, Now(), $7)
      RETURNING phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4;