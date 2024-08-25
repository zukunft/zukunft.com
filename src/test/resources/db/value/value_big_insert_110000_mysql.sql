PREPARE value_big_insert_110000 FROM
    'INSERT INTO values_big
                 (group_id, user_id, numeric_value, last_update)
          VALUES (?, ?, ?, Now())';