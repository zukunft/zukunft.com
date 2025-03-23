PREPARE value_prime_p3_by_usr_cfg FROM
   'SELECT phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           numeric_value,
           source_id,
           last_update,
           excluded,
           protect_id
      FROM user_values_prime
     WHERE phrase_id_1 = ?
       AND phrase_id_2 = ?
       AND phrase_id_3 = ?
       AND phrase_id_4 = ?
       AND user_id = ?
       AND source_id = ?';
