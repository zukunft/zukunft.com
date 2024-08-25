PREPARE view_term_link_update_000004
    (smallint,bigint) AS
        UPDATE view_term_links
           SET view_link_type_id = $1
         WHERE view_term_link_id = $2;