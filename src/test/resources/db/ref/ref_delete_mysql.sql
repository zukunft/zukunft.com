PREPARE ref_delete FROM
    'DELETE FROM refs
           WHERE ref_id = ?';