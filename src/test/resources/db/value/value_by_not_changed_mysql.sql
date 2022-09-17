PREPARE value_by_not_changed FROM
    'SELECT user_id
       FROM user_values
      WHERE value_id = ?
        AND (excluded <> 1 OR excluded is NULL)';