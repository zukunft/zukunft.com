PREPARE term_view_delete FROM
   'DELETE FROM term_views
          WHERE term_view_id = ?';