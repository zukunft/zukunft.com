PREPARE component_link_insert_0155010000 (bigint,bigint,bigint,bigint) AS
    INSERT INTO component_links (user_id,view_id,component_id,order_nbr)
         VALUES ($1,$2,$3,$4)
      RETURNING component_link_id;