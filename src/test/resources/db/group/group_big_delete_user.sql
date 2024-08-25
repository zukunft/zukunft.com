PREPARE group_big_delete_user (text, bigint) AS
    DELETE FROM user_groups_big
          WHERE group_id = $1
            AND user_id = $2;