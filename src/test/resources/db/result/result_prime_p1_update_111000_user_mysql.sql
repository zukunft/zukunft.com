PREPARE result_prime_p1_update_111000_user FROM
    'UPDATE user_results_prime
        SET numeric_value = ?,
            last_update = Now(),
            source_group_id = ?
      WHERE formula_id = ?
        AND phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?
        AND user_id = ?';