PREPARE db_cache_by_type_id_user_id (bigint, bigint, bigint, bigint) AS
    SELECT db_cache_id,
           type_id,
           data,
           user_id,
           status_id,
           last_update
      FROM db_caches
     WHERE type_id = $1
       AND user_id = $2
  ORDER BY last_update DESC
     LIMIT $3 OFFSET $4;