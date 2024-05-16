PREPARE view_update_001100000 FROM
    'UPDATE views
        SET view_name = ?,
            description = ?
      WHERE view_id = ?';