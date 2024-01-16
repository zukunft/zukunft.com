PREPARE value_big_changer (text, bigint) AS
    SELECT group_id,
           user_id
      FROM user_values_big
     WHERE group_id = $1
       AND (excluded <> $2 OR excluded IS NULL);