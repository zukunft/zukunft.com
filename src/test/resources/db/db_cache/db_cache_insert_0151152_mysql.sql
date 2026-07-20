PREPARE db_cache_insert_0151152 FROM
    'INSERT INTO db_caches
                (user_id,
                 type_id,
                 data,
                 status_id,
                 last_update)
         VALUES (?,?,?,?,?)';