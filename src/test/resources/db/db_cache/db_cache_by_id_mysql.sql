PREPARE db_cache_by_id FROM
    'SELECT db_cache_id,
            type_id,
            data,
            user_id,
            status_id,
            last_update
       FROM db_caches
      WHERE db_cache_id = ?';