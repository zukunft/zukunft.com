PREPARE result_by_usr_cfg FROM
   'SELECT phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4
      FROM user_results_prime
     WHERE phrase_id_1 = ?
       AND user_id = ?';