PREPARE value_prime_p1_user_update_val_upd FROM
    'UPDATE user_values_prime
        SET numeric_value = ?,last_update = Now()
      WHERE phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?
        AND user_id = ?';
