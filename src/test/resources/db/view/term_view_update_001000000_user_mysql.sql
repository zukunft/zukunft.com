PREPARE term_view_update_001000000_user FROM
    'UPDATE user_term_views
        SET description = ?
      WHERE term_view_id = ?
        AND user_id = ?';