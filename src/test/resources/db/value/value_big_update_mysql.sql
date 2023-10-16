PREPARE value_big_update FROM
    'UPDATE values_big
        SET numeric_value = ?,last_update = ?
      WHERE group_id = ?';
