PREPARE view_term_link_update_0004_user FROM
   'UPDATE user_view_term_links
       SET view_link_type_id = ?
     WHERE view_term_link_id = ?
       AND user_id = ?';