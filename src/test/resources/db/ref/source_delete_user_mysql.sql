PREPARE source_delete_user FROM
     'DELETE FROM user_sources
            WHERE source_id = ?
              AND user_id = ?';