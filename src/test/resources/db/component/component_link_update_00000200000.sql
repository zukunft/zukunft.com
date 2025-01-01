PREPARE component_link_update_00000200000 (bigint, bigint) AS
    UPDATE component_links
       SET order_nbr = $1
     WHERE component_link_id = $2;
