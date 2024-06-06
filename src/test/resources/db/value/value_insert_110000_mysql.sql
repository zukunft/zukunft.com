PREPARE value_insert_110000 FROM
    'INSERT INTO `values`
                 (group_id, user_id, numeric_value, last_update)
          VALUES (?, ?, ?, Now())';