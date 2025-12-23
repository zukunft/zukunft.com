PREPARE term_view_update_000004000 FROM
   'UPDATE term_views
       SET view_link_type_id = ?
     WHERE term_view_id = ?';