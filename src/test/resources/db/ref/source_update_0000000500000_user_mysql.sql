PREPARE source_update_0000000500000_user FROM
    'UPDATE user_sources
        SET view_id = ?
      WHERE source_id = ?
        AND user_id = ?';