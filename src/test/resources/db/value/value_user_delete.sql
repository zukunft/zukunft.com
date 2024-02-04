PREPARE value_user_delete (text) AS
    DELETE FROM user_values
     WHERE group_id = $1;