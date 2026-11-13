PREPARE user_update_clear_last_source_id FROM
   'UPDATE users
       SET source_id = ?
     WHERE source_id = ?';
