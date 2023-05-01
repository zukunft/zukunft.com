PREPARE view_component_link_by_usr_cfg (int, int) AS
    SELECT view_component_link_id,
           order_nbr,
           position_type,
           excluded,
           share_type_id,
           protect_id
      FROM user_view_component_links
     WHERE view_component_link_id = $1
       AND user_id = $2;
