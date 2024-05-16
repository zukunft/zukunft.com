PREPARE view_delete_excluded_user (bigint) AS
    DELETE FROM user_views
           WHERE view_id = $1
             AND excluded = 1;
