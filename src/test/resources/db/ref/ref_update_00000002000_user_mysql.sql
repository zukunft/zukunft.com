PREPARE ref_update_00000002000_user FROM
    'UPDATE user_refs
        SET description = ?
      WHERE ref_id = ?
        AND user_id = ?';