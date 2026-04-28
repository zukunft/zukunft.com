PREPARE db_cache_insert_0150152 (bigint,smallint,smallint,timestamp) AS
    INSERT INTO db_caches
                (user_id,
                 type_id,
                 status_id,
                 last_update)
         VALUES ($1,$2,$3,$4)
      RETURNING  db_cache_id;