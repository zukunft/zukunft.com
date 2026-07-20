PREPARE db_cache_page_update_1112 (bigint, text, text, timestamp, bigint) AS
    UPDATE db_cache_pages
       SET db_cache_page_id = $1,
           url              = $2,
           html_page        = $3,
           last_update      = $4
     WHERE db_cache_page_id = $5;