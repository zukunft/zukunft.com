PREPARE value_delete_user FROM
   'DELETE FROM user_values
     WHERE group_id = ?
       AND user_id = ?
       AND source_id = ?';