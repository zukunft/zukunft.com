PREPARE result_prime_p3_insert_111000 FROM
    'INSERT INTO results_prime (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, user_id, numeric_value, last_update, source_group_id)
          VALUES (?, ?, ?, ?, ?, ?, Now(), ?)';