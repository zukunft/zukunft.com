PREPARE value_by_median_user_user (text) AS
    SELECT group_id, user_id
      FROM user_values
    WHERE group_id = $1;