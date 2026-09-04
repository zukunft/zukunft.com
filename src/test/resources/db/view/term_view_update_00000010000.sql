PREPARE term_view_update_00000010000 (bigint, bigint) AS
    UPDATE term_views
       SET order_nbr = $1
     WHERE term_view_id = $2;