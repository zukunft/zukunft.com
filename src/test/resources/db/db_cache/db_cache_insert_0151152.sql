PREPARE db_cache_insert_0151152 (bigint,smallint,text,smallint,timestamp) AS
    INSERT INTO db_caches
                (user_id,
                 type_id,
                 data,
                 status_id,
                 last_update)
         VALUES ($1,$2,$3,$4,$5)
      RETURNING  db_cache_id;