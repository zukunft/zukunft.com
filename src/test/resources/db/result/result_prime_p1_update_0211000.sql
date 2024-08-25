PREPARE result_prime_p1_update_0211000
    (numeric, bigint, smallint, smallint, smallint, smallint, smallint) AS
    UPDATE results_prime
       SET numeric_value   = $1,
           last_update     = Now(),
           source_group_id = $2
     WHERE formula_id = $3
       AND phrase_id_1 = $4
       AND phrase_id_2 = $5
       AND phrase_id_3 = $6
       AND phrase_id_4 = $7;