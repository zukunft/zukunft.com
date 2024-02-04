PREPARE value_big_update_val_upd FROM
    'UPDATE values_big
        SET numeric_value = ?,last_update = Now()
      WHERE group_id = ?';
