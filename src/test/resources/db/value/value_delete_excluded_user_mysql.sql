PREPARE value_delete_excluded_user FROM
   'DELETE FROM user_values
     WHERE group_id = ?
       AND excluded = 1';