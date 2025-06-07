PREPARE term_view_delete_excluded FROM
   'DELETE FROM term_views
          WHERE term_view_id = ?
            AND excluded = 1';