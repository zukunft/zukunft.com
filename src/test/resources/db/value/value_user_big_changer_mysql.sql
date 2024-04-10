PREPARE value_user_big_changer FROM
    'SELECT group_id,
            user_id
       FROM user_values_big
      WHERE group_id = ?
        AND (excluded <> ? OR excluded IS NULL)';