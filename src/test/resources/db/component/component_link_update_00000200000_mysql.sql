PREPARE component_link_update_00000200000 FROM
   'UPDATE component_links
       SET order_nbr = ?
     WHERE component_link_id = ?';
