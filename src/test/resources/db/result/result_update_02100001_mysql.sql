PREPARE result_update_02100001 FROM
    'UPDATE results
        SET numeric_value = ?,
            last_update   = Now(),
            protect_id    = ?
      WHERE group_id = ?';