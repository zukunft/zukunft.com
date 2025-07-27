PREPARE value_big_by_usr_cfg FROM
    'SELECT group_id,
            numeric_value,
            source_id,
            last_update,
            excluded,
            protect_id
       FROM user_values_big
      WHERE group_id = ?
        AND user_id = ?
       AND source_id = ?';