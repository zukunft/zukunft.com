PREPARE term_view_update_0004_user
    (smallint, bigint, bigint) AS
        UPDATE user_term_views
           SET view_link_type_id = $1
         WHERE term_view_id = $2
           AND user_id = $3;