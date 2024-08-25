PREPARE result_prime_p1_delete (smallint, smallint, smallint, smallint, smallint) AS
    DELETE FROM results_prime
          WHERE formula_id = $1
            AND phrase_id_1 = $2
            AND phrase_id_2 = $3
            AND phrase_id_3 = $4
            AND phrase_id_4 = $5;