PREPARE value_text_insert_11000_user FROM
    'INSERT INTO user_values_text (group_id, user_id, source_id, text_value, last_update)
          VALUES                  (?, ?, ?, ?, Now())';