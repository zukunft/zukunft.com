PREPARE component_link_insert_01555150000 (bigint, bigint, bigint, smallint, bigint, smallint) AS
    INSERT INTO component_links (user_id, view_id, component_id, component_link_type_id, order_nbr, position_type_id)
         VALUES ($1,$2,$3,$4,$5,$6)
      RETURNING component_link_id;