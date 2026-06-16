PREPARE db_cache_delete (bigint) AS
    DELETE FROM db_caches
          WHERE db_cache_id = $1;