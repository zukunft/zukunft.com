PREPARE db_cache_page_by_id (bigint) AS
    SELECT     db_cache_page_id,
               url,
               html_page,
               last_update
          FROM db_cache_pages
         WHERE db_cache_page_id = $1;