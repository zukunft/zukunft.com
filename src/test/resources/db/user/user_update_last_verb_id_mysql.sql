PREPARE user_update_last_verb_id FROM
   'UPDATE users
       SET verb_id = ?
     WHERE user_id = ?';
