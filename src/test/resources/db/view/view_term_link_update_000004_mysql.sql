PREPARE view_term_link_update_000004 FROM
   'UPDATE view_term_links
       SET view_link_type_id = ?
     WHERE view_term_link_id = ?';