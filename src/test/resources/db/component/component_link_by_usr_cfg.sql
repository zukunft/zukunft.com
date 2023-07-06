PREPARE component_link_by_usr_cfg (int, int) AS
    SELECT component_link_id,
           order_nbr,
           position_type,
           excluded,
           share_type_id,
           protect_id
      FROM user_component_links
     WHERE component_link_id = $1
       AND user_id = $2;
