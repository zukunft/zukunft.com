PREPARE value_user_delete FROM
   'DELETE FROM user_values
     WHERE group_id = ?';