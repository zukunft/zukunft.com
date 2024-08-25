PREPARE result_standard_prime_p1_insert_010 FROM
    'INSERT INTO results_standard_prime
                 (formula_id, phrase_id_1, numeric_value)
          VALUES (?,?,?)';