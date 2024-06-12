PREPARE result_standard_prime_p1_update_020 FROM
    'UPDATE results_standard_prime
        SET numeric_value = ?
      WHERE formula_id = ?
        AND phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?';