PREPARE user_update_last_source_id FROM
   'UPDATE users
       SET source_id = ?
     WHERE user_id = ?';
