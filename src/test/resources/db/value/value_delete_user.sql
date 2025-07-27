PREPARE value_delete_user (text, bigint, bigint) AS
    DELETE FROM user_values
     WHERE group_id = $1
       AND user_id = $2
       AND source_id = $3;