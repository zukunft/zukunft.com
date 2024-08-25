PREPARE value_by_median_user_user FROM
    'SELECT group_id,user_id
       FROM user_values
      WHERE group_id = ?';