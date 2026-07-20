PREPARE db_cache_page_update_1112 FROM
    'UPDATE db_cache_pages
        SET db_cache_page_id = ?,
            `url`            = ?,
            html_page        = ?,
            last_update      = ?
      WHERE db_cache_page_id = ?';