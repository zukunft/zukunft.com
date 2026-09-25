PREPARE db_cache_delete_by_user FROM
    'DELETE FROM db_caches
           WHERE user_id = ?';