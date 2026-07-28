SELECT component_link_id,
       view_id,
       component_id,
       order_nbr,
       position_type_id,
       excluded
  FROM component_links
 WHERE component_link_id = ?;