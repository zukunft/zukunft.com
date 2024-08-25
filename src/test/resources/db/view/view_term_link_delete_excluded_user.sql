PREPARE view_term_link_delete_excluded_user (bigint) AS
    DELETE FROM user_view_term_links
          WHERE view_term_link_id = $1
            AND excluded = 1;