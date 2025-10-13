PREPARE source_update_00220022000_user FROM
    'UPDATE user_sources
        SET source_name = ?,
            description = ?,
            source_type_id = ?,
            `url` = ?
      WHERE source_id = ?
        AND user_id = ?';