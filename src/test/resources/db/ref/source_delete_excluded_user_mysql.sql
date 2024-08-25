PREPARE source_delete_excluded_user FROM
     'DELETE FROM user_sources
            WHERE source_id = ?
             AND excluded = 1';