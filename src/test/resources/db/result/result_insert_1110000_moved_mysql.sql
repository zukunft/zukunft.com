PREPARE result_insert_1110000 FROM
    'INSERT INTO results (group_id, user_id, numeric_value, last_update, source_group_id)
          VALUES         (?, ?, ?, Now(), ?)';