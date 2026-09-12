SELECT component_link_id,
       view_id,
       component_id,
       order_nbr,
       position_type_id,
       excluded
  FROM component_links
 WHERE view_id = $1 AND component_id = $2;