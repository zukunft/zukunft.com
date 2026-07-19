PREPARE db_cache_by_type_id (bigint, bigint, bigint) AS
    SELECT db_cache_id,
           type_id,
           data,
           user_id,
           status_id,
           last_update
      FROM db_caches
     WHERE type_id = $1
  ORDER BY last_update DESC
     LIMIT $2 OFFSET $3;