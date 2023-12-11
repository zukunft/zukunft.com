PREPARE value_prime_p1_user_update_val_upd FROM
    'UPDATE user_values_prime
        SET numeric_value = ?,last_update = Now()
      WHERE phrase_id_1 = ?';
