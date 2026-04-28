PREPARE db_cache_update_1150152 FROM
    'UPDATE db_caches
        SET db_cache_id = ?,
            type_id = ?,
            status_id = ?,
            user_id = ?,
            last_update = ?
      WHERE db_cache_id = ?';