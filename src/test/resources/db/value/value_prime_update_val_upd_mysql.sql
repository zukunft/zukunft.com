PREPARE value_prime_update_val_upd FROM
    'UPDATE values_prime
        SET numeric_value = ?,last_update = Now()
      WHERE group_id = ?';
