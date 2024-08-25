PREPARE view_term_link_delete_user (bigint,bigint) AS
    DELETE FROM user_view_term_links
          WHERE view_term_link_id = $1
            AND user_id = $2;