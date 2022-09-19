PREPARE value_by_usr_cfg FROM
   'SELECT value_id,
           word_value,
           source_id,
           last_update,
           excluded,
           protect_id
      FROM user_values
     WHERE value_id = ?
       AND user_id = ?';
