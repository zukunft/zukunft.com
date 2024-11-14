PREPARE component_link_insert_110100000_user FROM
    'INSERT INTO user_component_links (component_link_id,user_id,order_nbr)
          VALUES (?,?,?)';