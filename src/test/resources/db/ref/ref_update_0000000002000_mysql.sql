PREPARE ref_update_0000000002000 FROM
    'UPDATE refs
        SET description = ?
      WHERE ref_id = ?';