PREPARE source_user_delete FROM
     'DELETE FROM user_sources
            WHERE source_id = ?
              AND user_id = ?';