PREPARE term_view_update_0010000_user FROM
   'UPDATE user_term_views
       SET description = ?
     WHERE term_view_id = ?
       AND user_id = ?';