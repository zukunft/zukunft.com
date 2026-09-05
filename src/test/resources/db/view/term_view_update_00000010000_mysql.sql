PREPARE term_view_update_00000010000 FROM
    'UPDATE term_views
        SET order_nbr = ?
      WHERE term_view_id = ?';