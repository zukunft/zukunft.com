PREPARE db_cache_update_1151152 (bigint,smallint,text,smallint,bigint,timestamp,bigint) AS
    UPDATE db_caches
       SET db_cache_id = $1,
           type_id     = $2,
           data        = $3,
           status_id   = $4,
           user_id     = $5,
           last_update = $6
     WHERE db_cache_id = $7;