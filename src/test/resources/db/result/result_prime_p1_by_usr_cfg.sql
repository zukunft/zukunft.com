PREPARE result_prime_p1_by_usr_cfg (smallint, smallint, smallint, smallint, smallint, bigint) AS
    SELECT formula_id,
           phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4
      FROM user_results_prime
     WHERE formula_id = $1
       AND phrase_id_1 = $2
       AND phrase_id_2 = $3
       AND phrase_id_3 = $4
       AND phrase_id_4 = $5
       AND user_id = $6;
