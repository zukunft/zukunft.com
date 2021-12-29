SELECT     s.view_component_link_id,
           u.view_component_link_id  AS user_view_component_link_id,
           s.user_id,
           s.view_id,
           s.view_component_id,
           IF(u.order_nbr IS NULL, s.order_nbr, u.order_nbr)             AS order_nbr,
           IF(u.position_type IS NULL, s.position_type, u.position_type) AS position_type,
           IF(u.excluded IS NULL, s.excluded, u.excluded)                AS excluded,
           IF(u.share_type_id IS NULL, s.share_type_id, u.share_type_id) AS share_type_id,
           IF(u.protect_id IS NULL, s.protect_id, u.protect_id)          AS protect_id
      FROM view_component_links s
 LEFT JOIN user_view_component_links u ON s.view_component_link_id = u.view_component_link_id AND u.user_id = 1
     WHERE s.view_id = 1
       AND s.view_component_id = 2;