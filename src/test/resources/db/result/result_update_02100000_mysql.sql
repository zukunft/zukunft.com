PREPARE result_update_02100000 FROM
    'UPDATE results
        SET numeric_value = ?, last_update = Now()
      WHERE group_id = ?';