PREPARE value_big_insert_11000_user FROM
    'INSERT INTO user_values_big
                 (group_id, user_id, source_id, numeric_value, last_update)
          VALUES (?, ?, ?, ?, Now())';