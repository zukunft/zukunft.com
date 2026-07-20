PREPARE db_cache_by_type_id FROM
    'SELECT db_cache_id,
            type_id,
            data,
            user_id,
            status_id,
            last_update
       FROM db_caches
      WHERE type_id = ?
   ORDER BY last_update DESC
      LIMIT ? OFFSET ?';