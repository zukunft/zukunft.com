PREPARE value_prime_p1_update_val_upd FROM
    'UPDATE values_prime
        SET numeric_value = ?,last_update = Now()
      WHERE phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?';
