PREPARE term_view_update_00000005000 FROM
    'UPDATE term_views
        SET view_style_id = ?
      WHERE term_view_id = ?';