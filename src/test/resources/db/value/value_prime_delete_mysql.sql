PREPARE value_prime_delete FROM
   'DELETE FROM values_prime
     WHERE phrase_id_1 = ?
       AND phrase_id_2 = ?
       AND phrase_id_3 = ?
       AND phrase_id_4 = ?';