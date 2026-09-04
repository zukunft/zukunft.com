PREPARE term_view_update_00000005000 (smallint, bigint) AS
    UPDATE term_views
       SET view_style_id = $1
     WHERE term_view_id = $2;