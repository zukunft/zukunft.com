PREPARE value_big_by_not_changed (text) AS
    SELECT user_id
      FROM user_values_big
     WHERE group_id = $1
       AND (excluded <> 1 OR excluded is NULL);