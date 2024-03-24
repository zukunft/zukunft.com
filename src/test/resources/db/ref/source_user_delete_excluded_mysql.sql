PREPARE source_user_delete_excluded FROM
     'DELETE FROM user_sources
            WHERE source_id = ?
             AND excluded = 1';