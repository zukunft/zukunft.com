PREPARE view_update_00220000000 FROM
    'UPDATE views
        SET view_name = ?,
            description = ?
      WHERE view_id = ?';