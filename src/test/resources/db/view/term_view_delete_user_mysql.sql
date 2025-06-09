PREPARE term_view_delete_user FROM
    'DELETE FROM user_term_views
           WHERE term_view_id = ?
             AND user_id = ?';