PREPARE component_link_by_usr_cfg (bigint, bigint) AS
    SELECT component_link_id,
           order_nbr,
           position_type_id,
           view_style_id,
           excluded,
           share_type_id,
           protect_id
      FROM user_component_links
     WHERE component_link_id = $1
       AND user_id = $2;
