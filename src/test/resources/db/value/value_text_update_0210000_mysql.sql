PREPARE value_text_update_0210000 FROM
    'UPDATE values_text
        SET text_value  = ?,
            last_update = Now()
      WHERE group_id = ?';