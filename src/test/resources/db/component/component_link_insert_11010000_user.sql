PREPARE component_link_insert_11010000_user (bigint,bigint,bigint) AS
    INSERT INTO user_component_links (component_link_id,user_id,order_nbr)
         VALUES ($1,$2,$3)
      RETURNING component_link_id;