PREPARE component_link_insert_01555150000 FROM
    'INSERT INTO component_links (user_id, view_id, component_id, component_link_type_id, order_nbr, position_type_id)
          VALUES (?,?,?,?,?,?)';