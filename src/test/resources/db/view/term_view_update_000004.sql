PREPARE term_view_update_000004
    (smallint,bigint) AS
        UPDATE term_views
           SET view_link_type_id = $1
         WHERE term_view_id = $2;