PREPARE value_insert_11000_user FROM
    'INSERT INTO user_values
                 (group_id, user_id, source_id, numeric_value, last_update)
          VALUES (?, ?, ?, ?, Now())';