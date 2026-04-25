PREPARE term_view_update_000010000 FROM
   'UPDATE term_views
       SET description = ?
     WHERE term_view_id = ?';