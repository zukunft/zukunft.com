PREPARE value_prime_p1_delete_excluded_user FROM
   'DELETE FROM user_values_prime
     WHERE phrase_id_1 = ?
       AND phrase_id_2 = ?
       AND phrase_id_3 = ?
       AND phrase_id_4 = ?
       AND user_id = ?
       AND excluded = 1';