PREPARE value_update_0110000 FROM
    'UPDATE `values`
        SET numeric_value = ?,last_update = Now()
      WHERE group_id = ?';
