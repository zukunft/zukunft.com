SELECT max(m.order_nbr) AS max_order_nbr
  FROM ( SELECT CASE WHEN (u.order_nbr   IS NULL) THEN l.order_nbr   ELSE u.order_nbr   END AS order_nbr
           FROM component_links l
           LEFT JOIN user_component_links u ON u.component_link_id = l.component_link_id
                                           AND u.user_id = 3
          WHERE l.view_id = 1 ) AS m;