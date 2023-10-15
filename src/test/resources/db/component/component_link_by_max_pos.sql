PREPARE component_link_by_max_pos (bigint, bigint) AS
    SELECT max(g.order_nbr) AS max_order_nbr
      FROM ( SELECT CASE WHEN (u.order_nbr IS NULL) THEN s.order_nbr ELSE u.order_nbr END AS order_nbr
               FROM component_links s
          LEFT JOIN user_component_links u ON s.component_link_id = u.component_link_id
               AND u.user_id = $1
             WHERE s.view_id = $2 ) AS g;