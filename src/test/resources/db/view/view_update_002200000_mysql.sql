PREPARE view_update_002200000 FROM
    'UPDATE views
        SET view_name = ?,
            description = ?
      WHERE view_id = ?';