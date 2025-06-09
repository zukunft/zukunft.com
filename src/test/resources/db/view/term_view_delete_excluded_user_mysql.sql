PREPARE term_view_delete_excluded_user FROM
   'DELETE FROM user_term_views
          WHERE term_view_id = ?
            AND excluded = 1';