PREPARE value_big_insert FROM
    'INSERT INTO values_big
                 (group_id, user_id, numeric_value, last_update)
          VALUES (?, ?, ?, Now())';