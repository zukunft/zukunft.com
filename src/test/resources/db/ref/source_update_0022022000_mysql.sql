PREPARE source_update_0022022000 FROM
    'UPDATE sources
        SET source_name = ?,
            description = ?,
            source_type_id = ?,
            `url` = ?
      WHERE source_id = ?';