PREPARE component_link_by_usr_cfg FROM
   'SELECT component_link_id,
           order_nbr,
           position_type_id,
           excluded,
           share_type_id,
           protect_id
      FROM user_component_links
     WHERE component_link_id = ?
       AND user_id = ?';
