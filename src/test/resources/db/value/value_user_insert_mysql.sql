PREPARE value_user_insert FROM
    'INSERT INTO user_values
                 (group_id, user_id)
          VALUES (?, ?)';