PREPARE source_delete FROM
     'DELETE FROM sources
            WHERE source_id = ?';