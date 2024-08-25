PREPARE result_standard_prime_p1_insert_010 (smallint, smallint, numeric) AS
    INSERT INTO results_standard_prime (formula_id, phrase_id_1, numeric_value)
         VALUES ($1, $2, $3)
      RETURNING formula_id,phrase_id_1,phrase_id_2,phrase_id_3;