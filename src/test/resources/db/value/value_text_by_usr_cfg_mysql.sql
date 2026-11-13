PREPARE value_text_by_usr_cfg FROM
   'SELECT     group_id,
               text_value,
               source_id,
               last_update,
               excluded,
               protect_id
          FROM user_values_text
         WHERE group_id = ?
           AND user_id = ?
           AND source_id <=> ?';