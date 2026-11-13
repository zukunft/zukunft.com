PREPARE user_update_clear_last_verb_id FROM
   'UPDATE users
       SET verb_id = ?
     WHERE verb_id = ?';
