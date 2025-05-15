PREPARE result_prime_p1_by_usr_cfg FROM
   'SELECT formula_id,
           phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           last_update,
           excluded,
           share_type_id,
           protect_id
      FROM user_results_prime
     WHERE formula_id = ?
       AND phrase_id_1 = ?
       AND phrase_id_2 = ?
       AND phrase_id_3 = ?
       AND phrase_id_4 = ?
       AND user_id = ?';