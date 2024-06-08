PREPARE value_delete_excluded_user (text) AS
    DELETE FROM user_values
     WHERE group_id = $1
       AND excluded = 1;