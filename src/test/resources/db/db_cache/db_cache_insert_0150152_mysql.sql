PREPARE db_cache_insert_0150152 FROM
    'INSERT INTO db_caches
                (user_id,
                 type_id,
                 status_id,
                 last_update)
         VALUES (?,?,?,?)';