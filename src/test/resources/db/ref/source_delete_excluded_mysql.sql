PREPARE source_delete_excluded FROM
     'DELETE FROM sources
            WHERE source_id = ?
             AND excluded = 1';