PREPARE component_link_insert_01558100000 (bigint, bigint, bigint, smallint, bigint) AS
    INSERT INTO component_links (user_id, view_id, component_id, component_link_type_id, order_nbr)
         VALUES ($1,$2,$3,$4,$5)
      RETURNING component_link_id;