PREPARE result_prime_p1_by_grp FROM
   'SELECT formula_id,
           phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           user_id,
           source_group_id,
           numeric_value,
           last_update
      FROM results_prime
     WHERE phrase_id_1 = ?
       AND phrase_id_2 = ?
       AND phrase_id_3 = ?
       AND phrase_id_4 = ?';