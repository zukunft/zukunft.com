PREPARE result_prime_p1_insert_111000_user FROM
    'INSERT INTO user_results_prime (formula_id, phrase_id_1, user_id, numeric_value, last_update, source_group_id)
          VALUES (?, ?, ?, ?, Now(), ?)';