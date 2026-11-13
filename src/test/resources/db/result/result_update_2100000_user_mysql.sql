PREPARE result_update_2100000_user FROM
    'UPDATE user_results
        SET numeric_value = ?,
            last_update   = Now()
      WHERE group_id = ?
        AND user_id = ?';