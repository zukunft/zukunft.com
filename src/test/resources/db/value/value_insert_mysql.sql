PREPARE value_insert FROM
    'INSERT INTO `values`
                 (group_id, user_id, numeric_value, last_update)
          VALUES (?, ?, ?, ?)';