PREPARE db_cache_update_1151152 FROM
    'UPDATE db_caches
        SET db_cache_id = ?,
            type_id = ?,
            data = ?,
            status_id = ?,
            user_id = ?,
            last_update = ?
      WHERE db_cache_id = ?';