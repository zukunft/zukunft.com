PREPARE db_cache_update_1150152 (bigint,smallint,smallint,bigint,timestamp,bigint) AS
    UPDATE db_caches
       SET db_cache_id = $1,
           type_id     = $2,
           status_id   = $3,
           user_id     = $4,
           last_update = $5
     WHERE db_cache_id = $6;