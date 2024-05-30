PREPARE value_delete_user (text) AS
    DELETE FROM user_values
     WHERE group_id = $1;