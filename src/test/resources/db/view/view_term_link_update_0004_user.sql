PREPARE view_term_link_update_0004_user
    (smallint, bigint, bigint) AS
        UPDATE user_view_term_links
           SET view_link_type_id = $1
         WHERE view_term_link_id = $2
           AND user_id = $3;