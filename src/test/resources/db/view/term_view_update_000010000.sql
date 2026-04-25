PREPARE term_view_update_000010000
    (text,bigint) AS
        UPDATE term_views
           SET description = $1
         WHERE term_view_id = $2;
