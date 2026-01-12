PREPARE term_view_update_0010000_user
    (text, bigint, bigint) AS
        UPDATE user_term_views
           SET description = $1
         WHERE term_view_id = $2
           AND user_id = $3;