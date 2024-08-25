PREPARE result_delete_user (text, bigint) AS
    DELETE FROM user_results
          WHERE group_id = $1
            AND user_id = $2;