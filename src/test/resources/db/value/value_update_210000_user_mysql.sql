PREPARE value_update_210000_user FROM
    'UPDATE user_values
        SET numeric_value = ?,
            last_update   = Now()
      WHERE group_id = ?
        AND user_id = ?';