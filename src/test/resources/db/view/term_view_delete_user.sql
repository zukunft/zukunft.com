PREPARE term_view_delete_user (bigint,bigint) AS
    DELETE FROM user_term_views
          WHERE term_view_id = $1
            AND user_id = $2;