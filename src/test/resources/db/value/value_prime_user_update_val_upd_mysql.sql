PREPARE value_prime_user_update_val_upd FROM
    'UPDATE user_values_prime
        SET numeric_value = ?,last_update = Now()
      WHERE group_id = ?';
