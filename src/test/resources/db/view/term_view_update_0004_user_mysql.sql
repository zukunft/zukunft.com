PREPARE term_view_update_0004_user FROM
   'UPDATE user_term_views
       SET view_link_type_id = ?
     WHERE term_view_id = ?
       AND user_id = ?';