PREPARE result_big_insert_1110000 FROM
    'INSERT INTO results_big (group_id, user_id, numeric_value, last_update, source_group_id)
          VALUES             (?, ?, ?, Now(), ?)';