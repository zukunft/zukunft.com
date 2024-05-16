PREPARE view_delete_user (bigint, bigint) AS
    DELETE FROM user_views
           WHERE view_id = $1
             AND user_id = $2;
