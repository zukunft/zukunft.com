PREPARE component_link_insert_0155011000 (bigint,bigint,bigint,bigint,text) AS
    INSERT INTO component_links (user_id,view_id,component_id,order_nbr,position_type_id)
         VALUES ($1,$2,$3,$4,$5)
      RETURNING component_link_id;