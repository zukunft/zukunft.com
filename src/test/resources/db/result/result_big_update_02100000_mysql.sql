PREPARE result_big_update_02100000 FROM
    'UPDATE results_big
        SET numeric_value = ?,
            last_update   = Now()
      WHERE group_id = ?';
