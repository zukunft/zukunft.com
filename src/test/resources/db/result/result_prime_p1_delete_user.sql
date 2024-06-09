PREPARE result_prime_p1_delete_user (smallint, smallint, smallint, smallint, smallint, bigint) AS
    DELETE FROM user_results_prime
          WHERE formula_id = $1
            AND phrase_id_1 = $2
            AND phrase_id_2 = $3
            AND phrase_id_3 = $4
            AND phrase_id_4 = $5
            AND user_id = $6;