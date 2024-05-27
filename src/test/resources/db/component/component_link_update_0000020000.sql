PREPARE component_link_update_0000020000 (bigint, bigint) AS
    UPDATE component_links
       SET order_nbr = $1
     WHERE component_link_id = $2;
