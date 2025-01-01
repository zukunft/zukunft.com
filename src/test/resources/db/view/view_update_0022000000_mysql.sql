PREPARE view_update_0022000000 FROM
    'UPDATE views
        SET view_name = ?,
            description = ?
      WHERE view_id = ?';