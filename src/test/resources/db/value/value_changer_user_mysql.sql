PREPARE value_changer_user FROM
    'SELECT group_id,
            user_id
       FROM user_values
      WHERE group_id = ?
        AND (excluded <> ? OR excluded IS NULL)';