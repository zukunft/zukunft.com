PREPARE value_insert_111111 FROM
    'INSERT INTO `values`
                 (group_id, user_id, numeric_value, last_update, source_id, excluded, share_type_id, protect_id)
          VALUES (?, ?, ?, Now(), ?, ?, ?, ?)';