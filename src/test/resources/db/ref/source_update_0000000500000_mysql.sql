PREPARE source_update_0000000500000 FROM
    'UPDATE sources
        SET view_id = ?
      WHERE source_id = ?';