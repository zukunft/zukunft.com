PREPARE db_cache_delete_by_user (bigint) AS
    DELETE FROM db_caches
          WHERE user_id = $1;