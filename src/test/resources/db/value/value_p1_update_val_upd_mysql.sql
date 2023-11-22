PREPARE value_p1_update_val_upd FROM
    'UPDATE values_prime
        SET numeric_value = ?,last_update = Now()
      WHERE phrase_id_1 = ?';
