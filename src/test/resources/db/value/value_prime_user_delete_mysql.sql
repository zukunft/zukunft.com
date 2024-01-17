PREPARE value_prime_user_delete FROM
   'DELETE FROM user_values_prime
     WHERE phrase_id_1 = ?
       AND phrase_id_2 = ?
       AND phrase_id_3 = ?
       AND phrase_id_4 = ?';