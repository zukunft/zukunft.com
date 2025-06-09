PREPARE term_view_delete_excluded_user (bigint) AS
    DELETE FROM user_term_views
          WHERE term_view_id = $1
            AND excluded = 1;