PREPARE component_link_insert_01558100000 FROM
    'INSERT INTO component_links (user_id, view_id, component_id, component_link_type_id, order_nbr)
          VALUES (?,?,?,?,?)';