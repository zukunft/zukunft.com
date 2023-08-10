PREPARE component_link_by_max_pos FROM
   'SELECT max(g.order_nbr) AS max_order_nbr
    FROM ( SELECT IF(u.order_nbr IS NULL,s.order_nbr,u.order_nbr) AS order_nbr
             FROM component_links s
        LEFT JOIN user_component_links u ON s.component_link_id = u.component_link_id
              AND u.user_id = ?
            WHERE s.view_id = ? ) AS g';