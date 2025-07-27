PREPARE result_standard_prime_p1_update_0200000
    (numeric, smallint, smallint, smallint, smallint) AS
    UPDATE results_standard_prime
       SET numeric_value = $1
     WHERE formula_id = $2
       AND phrase_id_1 = $3
       AND phrase_id_2 = $4
       AND phrase_id_3 = $5;