PREPARE value_big_by_not_changed_not_owned (text, bigint) AS
    SELECT user_id
      FROM user_values_big
     WHERE group_id = $1
       AND (excluded <> 1 OR excluded is NULL)
       AND user_id <> $2;