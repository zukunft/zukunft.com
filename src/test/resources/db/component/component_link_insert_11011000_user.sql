PREPARE component_link_insert_11011000_user (bigint,bigint,bigint,text) AS
    INSERT INTO user_component_links (component_link_id,user_id,order_nbr,position_type_id)
         VALUES ($1,$2,$3,$4)
      RETURNING component_link_id;