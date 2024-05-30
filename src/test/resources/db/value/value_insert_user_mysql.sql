PREPARE value_insert_user FROM
    'INSERT INTO user_values
                 (group_id, user_id)
          VALUES (?, ?)';