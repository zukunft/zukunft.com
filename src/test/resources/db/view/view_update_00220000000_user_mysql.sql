PREPARE view_update_00220000000_user FROM
    'UPDATE user_views
        SET view_name = ?,
            description = ?
      WHERE view_id = ?
        AND user_id = ?';