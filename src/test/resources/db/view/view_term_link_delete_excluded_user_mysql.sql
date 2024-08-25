PREPARE view_term_link_delete_excluded_user FROM
   'DELETE FROM user_view_term_links
          WHERE view_term_link_id = ?
            AND excluded = 1';