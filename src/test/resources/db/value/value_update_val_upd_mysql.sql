PREPARE value_update_val_upd FROM
    'UPDATE `values`
        SET numeric_value = ?,last_update = Now()
      WHERE group_id = ?';
