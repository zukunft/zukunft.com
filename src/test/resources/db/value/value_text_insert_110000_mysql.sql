PREPARE value_text_insert_110000 FROM
    'INSERT INTO values_text (group_id, user_id, text_value, last_update)
          VALUES             (?, ?, ?, Now())';