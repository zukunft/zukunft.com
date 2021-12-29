SELECT     s.view_component_link_id,
           u.view_component_link_id                                                          AS user_view_component_link_id,
           s.user_id,
           s.view_id,
           s.view_component_id,
           CASE WHEN (u.order_nbr IS NULL) THEN s.order_nbr ELSE u.order_nbr END             AS order_nbr,
           CASE WHEN (u.position_type IS NULL) THEN s.position_type ELSE u.position_type END AS position_type,
           CASE WHEN (u.excluded IS NULL) THEN s.excluded ELSE u.excluded END                AS excluded,
           CASE WHEN (u.share_type_id IS NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
           CASE WHEN (u.protect_id IS NULL) THEN s.protect_id ELSE u.protect_id END          AS protect_id
      FROM view_component_links s
 LEFT JOIN user_view_component_links u ON s.view_component_link_id = u.view_component_link_id AND u.user_id = 1
     WHERE s.view_id = 1
       AND s.view_component_id = 2;