PREPARE value_update FROM
    'UPDATE `values`
        SET numeric_value = ?,last_update = ?
      WHERE group_id = ?';
