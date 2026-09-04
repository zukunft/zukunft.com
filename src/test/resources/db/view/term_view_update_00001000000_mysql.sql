PREPARE term_view_update_00001000000 FROM
    'UPDATE term_views
        SET description = ?
      WHERE term_view_id = ?';